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
         * 2. OBTENER WEBHOOK SECRET
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
         * 3. VALIDAR FIRMA DE MERCADO PAGO
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
         * 4. PROCESAR ORDER
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

            /*
             * Consultamos directamente la Order.
             */
            $orderClient = new OrderClient();

            $order = $orderClient->get(
                $dataId
            );

            if (empty($order) || empty($order->id)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió información de la Order.'
                );
            }

            /*
             * =====================================================
             * 5. BUSCAR LA VENTA
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
                    'message' => 'Order recibida, pero no existe una Sale asociada.',
                    'order_id' => $order->id,
                ], 200);
            }

            /*
             * =====================================================
             * 6. VALIDAR ESTADO DE LA ORDER
             * =====================================================
             */

            $orderStatus = $order->status ?? null;
            $orderStatusDetail = $order->status_detail ?? null;

            if ($orderStatus !== 'processed') {
                return response()->json([
                    'message' => 'La Order todavía no está procesada.',
                    'order_id' => $order->id,
                    'status' => $orderStatus,
                    'status_detail' => $orderStatusDetail,
                ], 200);
            }

            /*
             * =====================================================
             * 7. OBTENER PAYMENT
             * =====================================================
             *
             * La estructura ya fue comprobada directamente
             * contra Mercado Pago:
             *
             * transactions
             *   payments
             *     0
             */

            $payment = null;

            if (
                isset($order->transactions) &&
                isset($order->transactions->payments)
            ) {
                $payments = $order->transactions->payments;

                if (is_array($payments)) {
                    $payment = $payments[0] ?? null;
                } elseif (
                    $payments instanceof \Traversable
                ) {
                    foreach ($payments as $paymentItem) {
                        $payment = $paymentItem;
                        break;
                    }
                } else {
                    $payment = $payments[0] ?? null;
                }
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
            $paymentStatus = $payment->status ?? null;
            $paymentStatusDetail =
                $payment->status_detail ?? null;
            $paymentAmount = $payment->amount ?? null;

            if (empty($paymentId)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió el ID del pago.'
                );
            }

            /*
             * =====================================================
             * 9. VERIFICAR QUE EL PAGO ESTÉ ACREDITADO
             * =====================================================
             */

            if (
                $paymentStatus !== 'processed' ||
                $paymentStatusDetail !== 'accredited'
            ) {
                return response()->json([
                    'message' => 'El pago todavía no está acreditado.',
                    'order_id' => $order->id,
                    'payment_id' => $paymentId,
                    'status' => $paymentStatus,
                    'status_detail' => $paymentStatusDetail,
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
                throw new RuntimeException(
                    "El monto del pago ({$paymentAmount}) " .
                    "no coincide con el total de la venta ({$saleTotal})."
                );
            }

            /*
             * =====================================================
             * 11. PROCESAR VENTA EN UNA TRANSACCIÓN
             * =====================================================
             */

            DB::transaction(function () use (
                $sale,
                $paymentMethod = null,
                $paymentId,
                $paymentAmount
            ) {
                /*
                 * Volvemos a consultar la venta con bloqueo.
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
                 * Si ya está pagada, no hacemos absolutamente nada.
                 *
                 * Esto evita:
                 * - segundo SalePayment
                 * - segundo descuento de stock
                 */
                if ($lockedSale->status === 'paid') {
                    return;
                }

                /*
                 * =================================================
                 * 12. MÉTODO DE PAGO
                 * =================================================
                 */

                $paymentMethod = PaymentMethod::query()
                    ->where(
                        'code',
                        'mercadopago'
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

                if (! $paymentMethod) {
                    throw new RuntimeException(
                        'No existe un método de pago activo ' .
                        'con código "mercadopago".'
                    );
                }

                /*
                 * =================================================
                 * 13. EVITAR PAYMENT DUPLICADO
                 * =================================================
                 */

                $existingPayment = $lockedSale
                    ->payments()
                    ->where(
                        'reference',
                        (string) $paymentId
                    )
                    ->first();

                if (! $existingPayment) {
                    $lockedSale->payments()->create([
                        'payment_method_id' =>
                            $paymentMethod->id,

                        'amount' =>
                            $paymentAmount,

                        'reference' =>
                            (string) $paymentId,

                        'notes' =>
                            'Pago acreditado por Mercado Pago.',
                    ]);
                }

                /*
                 * =================================================
                 * 14. CARGAR PRODUCTOS CON BLOQUEO
                 * =================================================
                 */

                $lockedSale->load([
                    'items' => function ($query) {
                        $query->with([
                            'product' => function (
                                $productQuery
                            ) {
                                $productQuery->lockForUpdate();
                            },
                        ]);
                    },
                ]);

                /*
                 * =================================================
                 * 15. DESCONTAR STOCK
                 * =================================================
                 */

                foreach ($lockedSale->items as $item) {
                    $product = $item->product;

                    if (! $product) {
                        throw new RuntimeException(
                            "El producto de la venta " .
                            "#{$lockedSale->id} ya no existe."
                        );
                    }

                    if (
                        $product->stock <
                        $item->quantity
                    ) {
                        throw new RuntimeException(
                            "Stock insuficiente para " .
                            "\"{$product->name}\". " .
                            "Disponible: {$product->stock}. " .
                            "Solicitado: {$item->quantity}."
                        );
                    }

                    $product->decrement(
                        'stock',
                        $item->quantity
                    );
                }

                /*
                 * =================================================
                 * 16. MARCAR VENTA COMO PAGADA
                 * =================================================
                 */

                $lockedSale->update([
                    'status' => 'paid',
                    'sold_at' => now(),
                ]);
            });

            /*
             * =====================================================
             * 17. RESPUESTA EXITOSA
             * =====================================================
             */

            return response()->json([
                'message' =>
                    'Webhook procesado correctamente.',

                'order_id' =>
                    $order->id,

                'order_status' =>
                    $orderStatus,

                'order_status_detail' =>
                    $orderStatusDetail,

                'payment_id' =>
                    $paymentId,

                'payment_status' =>
                    $paymentStatus,

                'payment_status_detail' =>
                    $paymentStatusDetail,

                'payment_amount' =>
                    $paymentAmount,

                'sale_id' =>
                    $sale->id,

                'sale_status' =>
                    'paid',
            ], 200);

        } catch (MPApiException $exception) {
            $content = $exception
                ->getApiResponse()
                ->getContent();

            return response()->json([
                'message' =>
                    'Mercado Pago rechazó la consulta de la Order.',

                'error' =>
                    is_string($content)
                        ? $content
                        : json_encode(
                            $content,
                            JSON_UNESCAPED_UNICODE
                        ),
            ], 500);

        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' =>
                    'No fue posible procesar el Webhook.',

                'error' =>
                    $exception->getMessage(),
            ], 500);
        }
    }
}