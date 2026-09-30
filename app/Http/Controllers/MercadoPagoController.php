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
     * Recibe las notificaciones Webhook de Mercado Pago.
     */
    public function webhook(Request $request): JsonResponse
    {
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');
        $dataId = $request->query('data.id');

        if (
            empty($xSignature) ||
            empty($xRequestId) ||
            empty($dataId)
        ) {
            return response()->json([
                'message' => 'Datos de Webhook incompletos.',
            ], 400);
        }

        $secret = config(
            'services.mercadopago.webhook_secret'
        );

        if (empty($secret)) {
            return response()->json([
                'message' => 'Webhook secret no configurado.',
            ], 500);
        }

        /**
         * Validamos que la notificación realmente
         * provenga de Mercado Pago.
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

        try {
            /**
             * Configuramos el SDK con nuestro Access Token.
             */
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

            /**
             * Consultamos la Order directamente en Mercado Pago.
             *
             * data.id contiene el ID de la Order.
             */
            $orderClient = new OrderClient();

            $order = $orderClient->get(
                $dataId
            );

            if (empty($order->id)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió información de la Order.'
                );
            }

            /**
             * Buscamos nuestra venta mediante
             * mercadopago_order_id.
             */
            $sale = Sale::query()
                ->where(
                    'mercadopago_order_id',
                    $order->id
                )
                ->first();

            /**
             * Puede existir una notificación de una Order
             * que todavía no tengamos registrada.
             *
             * En ese caso respondemos 200 para que Mercado Pago
             * no siga reintentando innecesariamente.
             */
            if (! $sale) {
                return response()->json([
                    'message' => 'Order recibida, pero no existe una Sale asociada.',
                    'order_id' => $order->id,
                ], 200);
            }

            /**
             * Obtenemos el estado actual de la Order.
             */
            $orderStatus = $order->status ?? null;

            /**
             * Si la Order todavía no está procesada,
             * no modificamos nuestra venta.
             */
            if ($orderStatus !== 'processed') {
                return response()->json([
                    'message' => 'Order recibida, pero todavía no está procesada.',
                    'order_id' => $order->id,
                    'status' => $orderStatus,
                ], 200);
            }

            /**
             * La Order ya está procesada.
             *
             * Ahora actualizamos nuestra venta
             * de forma atómica.
             */
            DB::transaction(function () use (
                $sale,
                $order
            ) {
                /**
                 * Volvemos a consultar la venta con bloqueo
                 * para evitar que dos Webhooks simultáneos
                 * procesen el mismo pago.
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

                /**
                 * Si ya fue pagada, no hacemos nada.
                 *
                 * Esto evita crear dos SalePayment
                 * o descontar stock dos veces si Mercado Pago
                 * reenvía el mismo Webhook.
                 */
                if ($lockedSale->status === 'paid') {
                    return;
                }

                /**
                 * Buscamos el método de pago de Mercado Pago.
                 *
                 * Debe existir en tu catálogo de métodos de pago
                 * con code = mercadopago.
                 */
                $paymentMethod = PaymentMethod::query()
                    ->where('code', 'mercadopago')
                    ->where('is_active', true)
                    ->first();

                if (! $paymentMethod) {
                    throw new RuntimeException(
                        'No existe un método de pago activo con código "mercadopago".'
                    );
                }

                /**
                 * Obtenemos el primer pago registrado
                 * dentro de la Order.
                 */
                $payment = data_get(
                    $order,
                    'transactions.payments.0'
                );

                if (! $payment) {
                    throw new RuntimeException(
                        'Mercado Pago no devolvió información del pago.'
                    );
                }

                $paymentId = data_get(
                    $payment,
                    'id'
                );

                $paymentAmount = data_get(
                    $payment,
                    'amount'
                );

                $paymentStatus = data_get(
                    $payment,
                    'status'
                );

                /**
                 * Si Mercado Pago no devuelve un ID de pago,
                 * no podemos registrar SalePayment correctamente.
                 */
                if (empty($paymentId)) {
                    throw new RuntimeException(
                        'Mercado Pago no devolvió el ID del pago.'
                    );
                }

                /**
                 * Evitamos duplicar el SalePayment
                 * si el Webhook se recibe más de una vez.
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
                        'payment_method_id' => $paymentMethod->id,
                        'amount' => $paymentAmount !== null
                            ? (float) $paymentAmount
                            : (float) $lockedSale->total,
                        'reference' => (string) $paymentId,
                        'notes' => 'Pago confirmado por Mercado Pago.',
                    ]);
                }

                /**
                 * Cargamos los productos con bloqueo para
                 * evitar descontar stock incorrectamente.
                 */
                $lockedSale->load([
                    'items' => function ($query) {
                        $query->with([
                            'product' => function ($productQuery) {
                                $productQuery->lockForUpdate();
                            },
                        ]);
                    },
                ]);

                /**
                 * Descontamos el stock solamente una vez.
                 *
                 * Si la venta ya estuviera pagada, este bloque
                 * no se ejecutaría porque salimos arriba.
                 */
                foreach ($lockedSale->items as $item) {
                    $product = $item->product;

                    if (! $product) {
                        throw new RuntimeException(
                            "El producto de la venta #{$lockedSale->id} ya no existe."
                        );
                    }

                    if ($product->stock < $item->quantity) {
                        throw new RuntimeException(
                            "Stock insuficiente para el producto \"{$product->name}\"."
                        );
                    }

                    $product->decrement(
                        'stock',
                        $item->quantity
                    );
                }

                /**
                 * Finalmente marcamos la venta como pagada.
                 */
                $lockedSale->update([
                    'status' => 'paid',
                    'sold_at' => now(),
                ]);
            });

            return response()->json([
                'message' => 'Webhook procesado correctamente.',
                'order_id' => $order->id,
                'status' => $orderStatus,
            ], 200);
        } catch (MPApiException $exception) {
            return response()->json([
                'message' => 'Mercado Pago rechazó la consulta de la Order.',
                'error' => $exception
                    ->getApiResponse()
                    ->getContent(),
            ], 500);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible procesar el Webhook.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}