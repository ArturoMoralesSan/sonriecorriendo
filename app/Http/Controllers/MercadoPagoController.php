<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\WebHook\WebhookSignatureValidator;
use Throwable;

class MercadoPagoController extends Controller
{
    /**
     * Webhook de Mercado Pago.
     */
    public function webhook(Request $request): JsonResponse
    {
        $xSignature = $request->headers->get('x-signature');
        $xRequestId = $request->headers->get('x-request-id');

        /*
         * Mercado Pago puede enviar data_id en el query string.
         * Si no viene ahí, usamos data.id del body.
         */
        $dataId = $request->query('data_id')
            ?? data_get($request->input('data'), 'id');

        $type = $request->input('type');
        $action = $request->input('action');

        $secret = trim(
            (string) config('services.mercadopago.webhook_secret')
        );

        $accessToken = trim(
            (string) config('services.mercadopago.access_token')
        );

        Log::info('Mercado Pago Webhook recibido', [
            'method' => $request->method(),
            'path' => $request->path(),
            'data_id' => $dataId,
            'type' => $type,
            'action' => $action,
            'has_signature' => !empty($xSignature),
            'has_request_id' => !empty($xRequestId),
        ]);

        /*
         * Validar configuración.
         */
        if ($secret === '') {
            Log::error('Mercado Pago Webhook Secret no configurado.');

            return response()->json([
                'message' => 'Webhook secret not configured',
            ], 500);
        }

        if ($dataId === null || $dataId === '') {
            Log::warning('Mercado Pago Webhook sin data_id.', [
                'query' => $request->query(),
                'body' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Missing data_id',
            ], 400);
        }

        /*
         * Validar firma oficial de Mercado Pago.
         *
         * IMPORTANTE:
         * No modificamos data_id.
         * No usamos strtolower().
         * No calculamos HMAC manualmente.
         */
        try {
            WebhookSignatureValidator::validate(
                $xSignature,
                $xRequestId,
                $dataId,
                $secret
            );
        } catch (InvalidWebhookSignatureException $e) {
            Log::warning('Mercado Pago Webhook firma inválida.', [
                'data_id' => $dataId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Invalid webhook signature',
            ], 401);
        } catch (Throwable $e) {
            Log::error('Mercado Pago Webhook error de validación.', [
                'data_id' => $dataId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Webhook validation error',
            ], 500);
        }

        /*
         * Mercado Pago puede enviar diferentes tipos de notificación.
         * Solo procesamos Orders.
         */
        if ($type !== 'order') {
            Log::info('Mercado Pago Webhook ignorado.', [
                'type' => $type,
                'action' => $action,
                'data_id' => $dataId,
            ]);

            return response()->json([
                'message' => 'Webhook received',
            ], 200);
        }

        if ($accessToken === '') {
            Log::error('Mercado Pago Access Token no configurado.');

            return response()->json([
                'message' => 'Access token not configured',
            ], 500);
        }

        try {
            /*
             * Configurar SDK.
             */
            MercadoPagoConfig::setAccessToken($accessToken);

            $orderClient = new OrderClient();

            /*
             * Obtener la Order directamente desde Mercado Pago.
             */
            $order = $orderClient->get($dataId);

            if (!$order) {
                Log::warning('Mercado Pago Order no encontrada.', [
                    'data_id' => $dataId,
                ]);

                return response()->json([
                    'message' => 'Order not found',
                ], 404);
            }

            /*
             * Buscar la venta asociada.
             */
            $sale = Sale::where(
                'mercadopago_order_id',
                $dataId
            )->first();

            if (!$sale) {
                Log::warning('Venta no encontrada para Mercado Pago Order.', [
                    'data_id' => $dataId,
                ]);

                /*
                 * Respondemos 200 para evitar reintentos infinitos
                 * cuando todavía no existe la venta local.
                 */
                return response()->json([
                    'message' => 'Sale not found',
                ], 200);
            }

            /*
             * Validar estado de la Order.
             */
            $orderStatus = $order->status ?? null;
            $orderStatusDetail = $order->status_detail ?? null;

            if (
                $orderStatus !== 'processed' ||
                $orderStatusDetail !== 'accredited'
            ) {
                Log::info('Mercado Pago Order aún no acreditada.', [
                    'data_id' => $dataId,
                    'status' => $orderStatus,
                    'status_detail' => $orderStatusDetail,
                ]);

                return response()->json([
                    'message' => 'Order not accredited',
                ], 200);
            }

            /*
             * Obtener pagos de la Order.
             */
            $payments = data_get(
                $order,
                'transactions.payments'
            );

            if (empty($payments)) {
                Log::warning('Mercado Pago Order sin pagos.', [
                    'data_id' => $dataId,
                ]);

                return response()->json([
                    'message' => 'No payments found',
                ], 200);
            }

            /*
             * Tomamos el primer pago.
             */
            $payment = $payments[0];

            $paymentId = data_get($payment, 'id');
            $paymentStatus = data_get($payment, 'status');
            $paymentStatusDetail = data_get(
                $payment,
                'status_detail'
            );

            /*
             * Validar pago.
             */
            if (
                $paymentStatus !== 'processed' ||
                $paymentStatusDetail !== 'accredited'
            ) {
                Log::info('Mercado Pago Payment aún no acreditado.', [
                    'data_id' => $dataId,
                    'payment_id' => $paymentId,
                    'status' => $paymentStatus,
                    'status_detail' => $paymentStatusDetail,
                ]);

                return response()->json([
                    'message' => 'Payment not accredited',
                ], 200);
            }

            /*
             * Obtener monto pagado.
             */
            $paidAmount = (float) (
                data_get($payment, 'transaction_amount')
                ?? data_get($order, 'total_amount')
                ?? 0
            );

            $saleTotal = (float) $sale->total;

            /*
             * Validar monto.
             */
            if (round($paidAmount, 2) !== round($saleTotal, 2)) {
                Log::error('Monto de Mercado Pago no coincide con la venta.', [
                    'data_id' => $dataId,
                    'payment_id' => $paymentId,
                    'paid_amount' => $paidAmount,
                    'sale_total' => $saleTotal,
                ]);

                return response()->json([
                    'message' => 'Amount mismatch',
                ], 422);
            }

            /*
             * Procesar pago y stock dentro de una transacción.
             */
            DB::transaction(function () use (
                $sale,
                $paymentId,
                $paidAmount
            ) {
                /*
                 * Bloquear la venta para evitar procesamiento duplicado.
                 */
                $lockedSale = Sale::where('id', $sale->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Si ya está pagada, no hacemos nada.
                 */
                if ($lockedSale->status === 'paid') {
                    return;
                }

                /*
                 * Buscar método de pago Mercado Pago.
                 */
                $paymentMethod = PaymentMethod::where(
                    'code',
                    'mercadopago'
                )
                    ->where('active', true)
                    ->first();

                if (!$paymentMethod) {
                    throw new \RuntimeException(
                        'Payment method mercadopago not found or inactive.'
                    );
                }

                /*
                 * Evitar registrar dos veces el mismo pago.
                 */
                $existingPayment = DB::table('payments')
                    ->where('reference', (string) $paymentId)
                    ->lockForUpdate()
                    ->first();

                if ($existingPayment) {
                    $lockedSale->update([
                        'status' => 'paid',
                        'sold_at' => now(),
                    ]);

                    return;
                }

                /*
                 * Obtener productos de la venta.
                 */
                $items = $lockedSale->items()
                    ->lockForUpdate()
                    ->get();

                /*
                 * Validar y descontar inventario.
                 */
                foreach ($items as $item) {
                    $product = $item->product()
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw new \RuntimeException(
                            "Product {$item->product_id} not found."
                        );
                    }

                    $quantity = (int) $item->quantity;

                    if ($product->stock < $quantity) {
                        throw new \RuntimeException(
                            "Insufficient stock for product {$product->id}."
                        );
                    }

                    $product->decrement(
                        'stock',
                        $quantity
                    );
                }

                /*
                 * Registrar pago.
                 */
                DB::table('payments')->insert([
                    'sale_id' => $lockedSale->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $paidAmount,
                    'reference' => (string) $paymentId,
                    'paid_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                 * Marcar venta como pagada.
                 */
                $lockedSale->update([
                    'status' => 'paid',
                    'sold_at' => now(),
                ]);
            });

            Log::info('Mercado Pago Order procesada correctamente.', [
                'data_id' => $dataId,
                'payment_id' => $paymentId,
                'sale_id' => $sale->id,
                'amount' => $paidAmount,
            ]);

            return response()->json([
                'message' => 'Order processed',
            ], 200);

        } catch (MPApiException $e) {
            Log::error('Mercado Pago API error.', [
                'data_id' => $dataId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Mercado Pago API error',
            ], 502);

        } catch (Throwable $e) {
            Log::error('Mercado Pago Webhook processing error.', [
                'data_id' => $dataId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Webhook processing error',
            ], 500);
        }
    }
}