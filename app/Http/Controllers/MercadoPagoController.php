<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Webhook\WebhookSignatureValidator;
use RuntimeException;
use Throwable;

class MercadoPagoController extends Controller
{
    /**
     * Recibe y procesa las notificaciones Webhook de Mercado Pago.
     */
    public function webhook(Request $request): JsonResponse
    {

            \Log::info('Mercado Pago Webhook recibido', [
            'method' => $request->method(),
            'path' => $request->path(),
            'data_id' => $request->query('data.id'),
            'type' => $request->input('type'),
            'action' => $request->input('action'),
            'has_signature' => $request->hasHeader('x-signature'),
            'has_request_id' => $request->hasHeader('x-request-id'),
        ]);
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');
        $dataId = $request->query('data.id');

        /*
         * =========================================================
         * 1. VALIDAR DATOS DEL WEBHOOK
         * =========================================================
         */

        if (
            empty($xSignature) ||
            empty($xRequestId) ||
            empty($dataId)
        ) {
            return response()->json([
                'message' => 'Datos de Webhook incompletos.',
            ], 400);
        }

        /*
         * =========================================================
         * 2. WEBHOOK SECRET
         * =========================================================
         */

        $secret = config(
            'services.mercadopago.webhook_secret'
        );

        if (empty($secret)) {
            return response()->json([
                'message' => 'Webhook secret no configurado.',
            ], 500);
        }

        /*
         * =========================================================
         * 3. VALIDAR FIRMA
         * =========================================================
         */

        try {
            WebhookSignatureValidator::validate(
                $xSignature,
                $xRequestId,
                $dataId,
                $secret
            );
        } catch (InvalidWebhookSignatureException) {
            return response()->json([
                'message' => 'Firma de Webhook inválida.',
            ], 401);
        }

        /*
         * =========================================================
         * 4. CONSULTAR ORDER EN MERCADO PAGO
         * =========================================================
         */

        try {
            $accessToken = config(
                'services.mercadopago.access_token'
            );

            if (empty($accessToken)) {
                throw new RuntimeException(
                    'Mercado Pago Access Token no está configurado.'
                );
            }

            MercadoPagoConfig::setAccessToken(
                $accessToken
            );

            $orderClient = new OrderClient();

            $order = $orderClient->get(
                $dataId
            );

            if (
                ! $order ||
                empty($order->id)
            ) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió información de la Order.'
                );
            }

            /*
             * =====================================================
             * 5. BUSCAR SALE
             * =====================================================
             */

            $sale = Sale::query()
                ->where(
                    'mercadopago_order_id',
                    (string) $order->id
                )
                ->first();

            if (! $sale) {
                return response()->json([
                    'message' =>
                        'Order recibida, pero no existe una Sale asociada.',

                    'order_id' =>
                        $order->id,
                ], 200);
            }

            /*
             * =====================================================
             * 6. VALIDAR ESTADO DE ORDER
             * =====================================================
             */

            $orderStatus = $order->status ?? null;

            $orderStatusDetail =
                $order->status_detail ?? null;

            if (
                $orderStatus !== 'processed' ||
                $orderStatusDetail !== 'accredited'
            ) {
                return response()->json([
                    'message' =>
                        'La Order todavía no tiene un pago acreditado.',

                    'order_id' =>
                        $order->id,

                    'status' =>
                        $orderStatus,

                    'status_detail' =>
                        $orderStatusDetail,
                ], 200);
            }

            /*
             * =====================================================
             * 7. OBTENER PAYMENT
             * =====================================================
             */

            $payments =
                $order->transactions->payments ?? null;

            if (! $payments) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió la lista de pagos.'
                );
            }

            $payment = null;

            if (is_array($payments)) {
                $payment = $payments[0] ?? null;
            } elseif ($payments instanceof \Traversable) {
                foreach ($payments as $item) {
                    $payment = $item;
                    break;
                }
            } else {
                $payment = $payments[0] ?? null;
            }

            if (! $payment) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió información del pago.'
                );
            }

            /*
             * =====================================================
             * 8. DATOS DEL PAYMENT
             * =====================================================
             */

            $paymentId = $payment->id ?? null;

            $paymentStatus =
                $payment->status ?? null;

            $paymentStatusDetail =
                $payment->status_detail ?? null;

            $paymentAmount =
                $payment->amount ?? null;

            if (empty($paymentId)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió el ID del pago.'
                );
            }

            /*
             * =====================================================
             * 9. VALIDAR PAYMENT
             * =====================================================
             */

            if (
                $paymentStatus !== 'processed' ||
                $paymentStatusDetail !== 'accredited'
            ) {
                return response()->json([
                    'message' =>
                        'El pago todavía no está acreditado.',

                    'payment_id' =>
                        $paymentId,

                    'status' =>
                        $paymentStatus,

                    'status_detail' =>
                        $paymentStatusDetail,
                ], 200);
            }

            /*
             * =====================================================
             * 10. VALIDAR MONTO
             * =====================================================
             */

            if ($paymentAmount === null) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió el monto del pago.'
                );
            }

            $paymentAmount = (float) $paymentAmount;

            $saleTotal = (float) $sale->total;

            if (
                round($paymentAmount, 2) !==
                round($saleTotal, 2)
            ) {
                return response()->json([
                    'message' =>
                        'El monto del pago no coincide con el total de la venta.',

                    'payment_id' =>
                        $paymentId,

                    'payment_amount' =>
                        $paymentAmount,

                    'sale_total' =>
                        $saleTotal,
                ], 422);
            }

            /*
             * =========================================================
             * 11. PROCESAR VENTA
             * =========================================================
             */

            DB::transaction(function () use (
                $sale,
                $payment,
                $paymentId,
                $paymentAmount
            ) {
                /*
                 * Bloqueamos la venta para evitar
                 * procesamiento duplicado del webhook.
                 */
                $lockedSale = Sale::query()
                    ->whereKey($sale->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedSale) {
                    throw new RuntimeException(
                        'La venta ya no existe.'
                    );
                }

                /*
                 * Si ya fue pagada, no hacemos nada.
                 *
                 * Esto permite que Mercado Pago envíe
                 * varias veces el mismo Webhook sin
                 * descontar stock nuevamente.
                 */
                if ($lockedSale->status === 'paid') {
                    return;
                }

                /*
                 * =====================================================
                 * 12. PAYMENT METHOD
                 * =====================================================
                 */

                $paymentMethod = PaymentMethod::query()
                    ->where('code', 'mercadopago')
                    ->where('is_active', true)
                    ->first();

                if (! $paymentMethod) {
                    throw new RuntimeException(
                        'No existe un método de pago activo para Mercado Pago.'
                    );
                }

                /*
                 * =====================================================
                 * 13. EVITAR PAYMENT DUPLICADO
                 * =====================================================
                 */

                $existingPayment = $lockedSale
                    ->payments()
                    ->where(
                        'reference',
                        (string) $paymentId
                    )
                    ->exists();

                if (! $existingPayment) {
                    $lockedSale->payments()->create([
                        'payment_method_id' =>
                            $paymentMethod->id,

                        'amount' =>
                            $paymentAmount,

                        'reference' =>
                            (string) $paymentId,

                        'notes' =>
                            'Pago acreditado mediante Mercado Pago.',
                    ]);
                }

                /*
                 * =====================================================
                 * 14. OBTENER PRODUCTOS Y BLOQUEAR STOCK
                 * =====================================================
                 */

                $lockedSale->load('items');

                foreach ($lockedSale->items as $saleItem) {
                    $product = $saleItem->product()
                        ->lockForUpdate()
                        ->first();

                    if (! $product) {
                        throw new RuntimeException(
                            "El producto asociado a la venta no existe."
                        );
                    }

                    if (
                        (int) $product->stock <
                        (int) $saleItem->quantity
                    ) {
                        throw new RuntimeException(
                            "No hay suficiente stock para el producto " .
                            "\"{$product->name}\"."
                        );
                    }

                    $product->decrement(
                        'stock',
                        (int) $saleItem->quantity
                    );
                }

                /*
                 * =====================================================
                 * 15. MARCAR VENTA COMO PAGADA
                 * =====================================================
                 */

                $lockedSale->update([
                    'status' => 'paid',
                    'sold_at' => now(),
                ]);
            });

            /*
             * =========================================================
             * 16. RESPUESTA EXITOSA
             * =========================================================
             */

            return response()->json([
                'message' =>
                    'Webhook procesado correctamente.',

                'order_id' =>
                    $order->id,

                'payment_id' =>
                    $paymentId,

                'sale_id' =>
                    $sale->id,

                'status' =>
                    'paid',
            ], 200);
        } catch (MPApiException $exception) {
            return response()->json([
                'message' =>
                    'Mercado Pago rechazó la consulta de la Order.',
            ], 502);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' =>
                    'No fue posible procesar el Webhook.',
            ], 500);
        }
    }
}