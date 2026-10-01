<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Webhook\WebhookSignatureValidator; // OJO: "Webhook", no "WebHook"
use RuntimeException;
use Throwable;

class MercadoPagoController extends Controller
{
    /**
     * Recibe y procesa las notificaciones Webhook de Mercado Pago (Orders API).
     */
    public function webhook(Request $request): JsonResponse
    {
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        $dataId = $request->query('data_id')
            ?? $request->query('data.id')
            ?? data_get($request->input('data'), 'id');

        $dataId = $dataId !== null ? trim((string) $dataId) : null;

        $type = $request->input('type');
        $action = $request->input('action');

        Log::info('Mercado Pago Webhook recibido', [
            'data_id' => $dataId,
            'type' => $type,
            'action' => $action,
            'live_mode' => $request->input('live_mode'),
            'application_id' => $request->input('application_id'),
            'has_signature' => ! empty($xSignature),
            'has_request_id' => ! empty($xRequestId),
        ]);

        /*
        |----------------------------------------------------------------------
        | 1. Validar datos mínimos
        |----------------------------------------------------------------------
        */

        if (empty($xSignature) || empty($xRequestId) || empty($dataId)) {
            Log::warning('Mercado Pago Webhook rechazado: datos incompletos.', [
                'has_signature' => ! empty($xSignature),
                'has_request_id' => ! empty($xRequestId),
                'data_id' => $dataId,
            ]);

            return response()->json(['message' => 'Datos de Webhook incompletos.'], 400);
        }

        $secret = trim((string) config('services.mercadopago.webhook_secret'));

        if ($secret === '') {
            Log::error('Mercado Pago Webhook: Webhook Secret no configurado.');

            return response()->json(['message' => 'Webhook secret no configurado.'], 500);
        }

        /*
        |----------------------------------------------------------------------
        | 2. Validar firma con el SDK
        |----------------------------------------------------------------------
        |
        | Mercado Pago indica que un data.id alfanumérico va en minúsculas en
        | el manifest. Se prueba primero con minúsculas y, si falla, con el
        | ID original. Se registra cuál variante funcionó para dejar de adivinar.
        |
        */

        $variants = array_values(array_unique([
            'lowercase' => strtolower($dataId),
            'original' => $dataId,
        ]));

        $validatedWith = null;
        $lastMessage = null;

        foreach ($variants as $candidate) {
            try {
                WebhookSignatureValidator::validate(
                    $xSignature,
                    $xRequestId,
                    $candidate,
                    $secret
                );

                $validatedWith = $candidate === $dataId && $candidate !== strtolower($dataId)
                    ? 'original'
                    : 'lowercase';

                break;
            } catch (InvalidWebhookSignatureException $exception) {
                $lastMessage = $exception->getMessage();
            } catch (Throwable $exception) {
                Log::error('Mercado Pago Webhook: error al validar la firma.', [
                    'data_id' => $dataId,
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                ]);

                return response()->json(['message' => 'Error al validar el Webhook.'], 500);
            }
        }

        if ($validatedWith === null) {
            Log::warning('Mercado Pago Webhook firma inválida.', [
                'data_id' => $dataId,
                'message' => $lastMessage,
                'live_mode' => $request->input('live_mode'),
                'application_id' => $request->input('application_id'),
            ]);

            return response()->json(['message' => 'Firma de Webhook inválida.'], 401);
        }

        Log::info('Mercado Pago Webhook firma validada.', [
            'data_id' => $dataId,
            'validated_with' => $validatedWith,
        ]);

        /*
        |----------------------------------------------------------------------
        | 3. Solo procesamos Orders
        |----------------------------------------------------------------------
        */

        if ($type !== 'order') {
            Log::info('Mercado Pago Webhook ignorado.', [
                'type' => $type,
                'action' => $action,
                'data_id' => $dataId,
            ]);

            return response()->json(['message' => 'Webhook recibido.'], 200);
        }

        /*
        |----------------------------------------------------------------------
        | 4. Consultar la Order y procesar
        |----------------------------------------------------------------------
        */

        try {
            $accessToken = trim((string) config('services.mercadopago.access_token'));

            if ($accessToken === '') {
                throw new RuntimeException('Mercado Pago Access Token no está configurado.');
            }

            MercadoPagoConfig::setAccessToken($accessToken);

            // Para consultar la API se usa el ID ORIGINAL (no el de minúsculas).
            $order = (new OrderClient())->get($dataId);

            if (! $order || empty($order->id)) {
                throw new RuntimeException('Mercado Pago no devolvió información de la Order.');
            }

            $orderId = (string) $order->id;

            $sale = Sale::query()
                ->where('mercadopago_order_id', $orderId)
                ->first();

            if (! $sale) {
                Log::warning('Mercado Pago Order sin Sale asociada.', ['order_id' => $orderId]);

                // 200 para evitar reintentos infinitos.
                return response()->json([
                    'message' => 'Order recibida, pero no existe una Sale asociada.',
                    'order_id' => $orderId,
                ], 200);
            }

            $orderStatus = $order->status ?? null;
            $orderStatusDetail = $order->status_detail ?? null;

            if ($orderStatus !== 'processed' || $orderStatusDetail !== 'accredited') {
                Log::info('Mercado Pago Order todavía no acreditada.', [
                    'order_id' => $orderId,
                    'status' => $orderStatus,
                    'status_detail' => $orderStatusDetail,
                ]);

                return response()->json([
                    'message' => 'La Order todavía no tiene un pago acreditado.',
                    'order_id' => $orderId,
                ], 200);
            }

            /*
            | Primer pago de la Order.
            */

            $payments = data_get($order, 'transactions.payments');

            $payment = null;

            if (is_array($payments)) {
                $payment = $payments[0] ?? null;
            } elseif ($payments instanceof \Traversable) {
                foreach ($payments as $item) {
                    $payment = $item;
                    break;
                }
            }

            if (! $payment) {
                throw new RuntimeException('Mercado Pago no devolvió información del pago.');
            }

            $paymentId = data_get($payment, 'id');
            $paymentStatus = data_get($payment, 'status');
            $paymentStatusDetail = data_get($payment, 'status_detail');

            if (empty($paymentId)) {
                throw new RuntimeException('Mercado Pago no devolvió el ID del pago.');
            }

            if ($paymentStatus !== 'processed' || $paymentStatusDetail !== 'accredited') {
                Log::info('Mercado Pago Payment todavía no acreditado.', [
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                    'status' => $paymentStatus,
                    'status_detail' => $paymentStatusDetail,
                ]);

                return response()->json([
                    'message' => 'El pago todavía no está acreditado.',
                    'payment_id' => $paymentId,
                ], 200);
            }

            /*
            | Validar monto.
            */

            $paidAmount = data_get($payment, 'amount')
                ?? data_get($payment, 'transaction_amount')
                ?? data_get($order, 'total_amount');

            if ($paidAmount === null) {
                throw new RuntimeException('Mercado Pago no devolvió el monto del pago.');
            }

            $paidAmount = (float) $paidAmount;
            $saleTotal = (float) $sale->total;

            if (round($paidAmount, 2) !== round($saleTotal, 2)) {
                Log::error('Mercado Pago: monto del pago no coincide.', [
                    'sale_id' => $sale->id,
                    'payment_id' => $paymentId,
                    'payment_amount' => $paidAmount,
                    'sale_total' => $saleTotal,
                ]);

                return response()->json([
                    'message' => 'El monto del pago no coincide con el total de la venta.',
                ], 422);
            }

            /*
            | Registrar pago, descontar stock y marcar venta como pagada.
            */

            DB::transaction(function () use ($sale, $paymentId, $paidAmount) {
                $lockedSale = Sale::query()
                    ->whereKey($sale->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedSale->status === 'paid') {
                    Log::info('Mercado Pago: venta ya estaba pagada.', [
                        'sale_id' => $lockedSale->id,
                    ]);

                    return;
                }

                $paymentMethod = PaymentMethod::query()
                    ->where('code', 'mercadopago')
                    ->where('is_active', true) // ajusta si tu columna se llama "active"
                    ->first();

                if (! $paymentMethod) {
                    throw new RuntimeException(
                        'No existe un método de pago activo para Mercado Pago.'
                    );
                }

                $paymentExists = $lockedSale->payments()
                    ->where('reference', (string) $paymentId)
                    ->exists();

                if (! $paymentExists) {
                    $lockedSale->payments()->create([
                        'payment_method_id' => $paymentMethod->id,
                        'amount' => $paidAmount,
                        'reference' => (string) $paymentId,
                        'notes' => 'Pago acreditado mediante Mercado Pago.',
                    ]);
                }

                $lockedSale->load('items');

                foreach ($lockedSale->items as $saleItem) {
                    $product = $saleItem->product()->lockForUpdate()->first();

                    if (! $product) {
                        throw new RuntimeException(
                            'El producto asociado a la venta no existe.'
                        );
                    }

                    $quantity = (int) $saleItem->quantity;

                    if ((int) $product->stock < $quantity) {
                        throw new RuntimeException(
                            "No hay suficiente stock para el producto \"{$product->name}\"."
                        );
                    }

                    $product->decrement('stock', $quantity);
                }

                $lockedSale->update([
                    'status' => 'paid',
                    'sold_at' => now(),
                ]);
            });

            Log::info('Mercado Pago Webhook procesado correctamente.', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'sale_id' => $sale->id,
            ]);

            return response()->json([
                'message' => 'Webhook procesado correctamente.',
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'sale_id' => $sale->id,
                'status' => 'paid',
            ], 200);
        } catch (MPApiException $exception) {
            Log::error('Mercado Pago: error al consultar la Order.', [
                'data_id' => $dataId,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Mercado Pago rechazó la consulta de la Order.',
            ], 502);
        } catch (Throwable $exception) {
            Log::error('Mercado Pago: error procesando Webhook.', [
                'data_id' => $dataId,
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            report($exception);

            return response()->json([
                'message' => 'No fue posible procesar el Webhook.',
            ], 500);
        }
    }
}