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
        /*
        |--------------------------------------------------------------------------
        | 1. DATOS DEL WEBHOOK
        |--------------------------------------------------------------------------
        */

        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        /*
         * IMPORTANTE:
         *
         * Mercado Pago puede enviar data_id como query parameter.
         *
         * NO hacemos strtolower() aquí.
         *
         * Conservamos exactamente el valor recibido.
         */
        $dataId = $request->query('data_id')
            ?? data_get($request->input('data'), 'id');

        $type = $request->input('type');
        $action = $request->input('action');

        Log::info('Mercado Pago Webhook recibido', [
            'method' => $request->method(),
            'path' => $request->path(),
            'data_id' => $dataId,
            'type' => $type,
            'action' => $action,
            'has_signature' => $request->hasHeader('x-signature'),
            'has_request_id' => $request->hasHeader('x-request-id'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. DATOS DE LA APLICACIÓN
        |--------------------------------------------------------------------------
        |
        | Solo diagnóstico.
        |
        */

        Log::info('Mercado Pago Application Debug', [
            'application_id' => $request->input('application_id'),
            'live_mode' => $request->input('live_mode'),
            'user_id' => $request->input('user_id'),
            'type' => $type,
            'action' => $action,
            'data_id' => $dataId,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3. VALIDAR DATOS DEL WEBHOOK
        |--------------------------------------------------------------------------
        */

        if (
            empty($xSignature) ||
            empty($xRequestId) ||
            empty($dataId)
        ) {
            Log::warning(
                'Mercado Pago Webhook rechazado: datos incompletos.',
                [
                    'has_signature' => ! empty($xSignature),
                    'has_request_id' => ! empty($xRequestId),
                    'data_id' => $dataId,
                ]
            );

            return response()->json([
                'message' => 'Datos de Webhook incompletos.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. WEBHOOK SECRET
        |--------------------------------------------------------------------------
        */

        $secret = config(
            'services.mercadopago.webhook_secret'
        );

        if (empty($secret)) {
            Log::error(
                'Mercado Pago Webhook: Webhook Secret no configurado.'
            );

            return response()->json([
                'message' => 'Webhook secret no configurado.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. PRUEBA DE FIRMA
        |--------------------------------------------------------------------------
        |
        | Comparamos:
        |
        | 1. data_id original
        | 2. data_id lowercase
        | 3. data_id uppercase
        |
        | Esto es SOLO diagnóstico.
        |
        */

        $signatureParts = [];

        foreach (explode(',', $xSignature) as $part) {
            [$key, $value] = array_pad(
                explode('=', trim($part), 2),
                2,
                null
            );

            if ($key !== null) {
                $signatureParts[strtolower(trim($key))] =
                    $value !== null
                        ? trim($value)
                        : null;
            }
        }

        $ts = $signatureParts['ts'] ?? null;
        $v1 = $signatureParts['v1'] ?? null;

        if (! $ts || ! $v1) {
            Log::warning(
                'Mercado Pago: x-signature sin ts o v1.',
                [
                    'data_id' => $dataId,
                ]
            );
        } else {
            $dataIdOriginal = (string) $dataId;
            $dataIdLower = strtolower($dataIdOriginal);
            $dataIdUpper = strtoupper($dataIdOriginal);

            /*
            |--------------------------------------------------------------------------
            | ORIGINAL
            |--------------------------------------------------------------------------
            */

            $manifestOriginal = sprintf(
                'id:%s;request-id:%s;ts:%s;',
                $dataIdOriginal,
                $xRequestId,
                $ts
            );

            $signatureOriginal = hash_hmac(
                'sha256',
                $manifestOriginal,
                $secret
            );

            /*
            |--------------------------------------------------------------------------
            | LOWERCASE
            |--------------------------------------------------------------------------
            */

            $manifestLower = sprintf(
                'id:%s;request-id:%s;ts:%s;',
                $dataIdLower,
                $xRequestId,
                $ts
            );

            $signatureLower = hash_hmac(
                'sha256',
                $manifestLower,
                $secret
            );

            /*
            |--------------------------------------------------------------------------
            | UPPERCASE
            |--------------------------------------------------------------------------
            */

            $manifestUpper = sprintf(
                'id:%s;request-id:%s;ts:%s;',
                $dataIdUpper,
                $xRequestId,
                $ts
            );

            $signatureUpper = hash_hmac(
                'sha256',
                $manifestUpper,
                $secret
            );

            /*
            |--------------------------------------------------------------------------
            | RESULTADO DEL TEST
            |--------------------------------------------------------------------------
            */

            $originalMatches = hash_equals(
                $signatureOriginal,
                $v1
            );

            $lowerMatches = hash_equals(
                $signatureLower,
                $v1
            );

            $upperMatches = hash_equals(
                $signatureUpper,
                $v1
            );

            Log::info(
                'Mercado Pago Signature Case Test',
                [
                    'data_id_original' => $dataIdOriginal,
                    'data_id_lower' => $dataIdLower,
                    'data_id_upper' => $dataIdUpper,

                    'received_prefix' =>
                        substr($v1, 0, 12) . '...',

                    'original_prefix' =>
                        substr($signatureOriginal, 0, 12) . '...',

                    'lower_prefix' =>
                        substr($signatureLower, 0, 12) . '...',

                    'upper_prefix' =>
                        substr($signatureUpper, 0, 12) . '...',

                    'original_matches' =>
                        $originalMatches,

                    'lower_matches' =>
                        $lowerMatches,

                    'upper_matches' =>
                        $upperMatches,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 6. VALIDAR FIRMA CON SDK
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | Mercado Pago SDK 3.16.0:
        |
        | validate(
        |     xSignature,
        |     xRequestId,
        |     dataId,
        |     secret
        | )
        |
        | El quinto parámetro NO es topic ni URI.
        |
        */

        try {
            WebhookSignatureValidator::validate(
                $xSignature,
                $xRequestId,
                $dataId,
                $secret
            );

            Log::info(
                'Mercado Pago Webhook firma validada.',
                [
                    'data_id' => $dataId,
                ]
            );
        } catch (InvalidWebhookSignatureException $exception) {
            Log::warning(
                'Mercado Pago Webhook firma inválida.',
                [
                    'data_id' => $dataId,
                    'message' => $exception->getMessage(),
                ]
            );

            return response()->json([
                'message' => 'Firma de Webhook inválida.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. SOLO PROCESAR ORDERS
        |--------------------------------------------------------------------------
        */

        if ($type !== 'order') {
            Log::info(
                'Mercado Pago Webhook ignorado: tipo no soportado.',
                [
                    'type' => $type,
                    'action' => $action,
                    'data_id' => $dataId,
                ]
            );

            return response()->json([
                'message' => 'Webhook recibido.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. CONSULTAR ORDER EN MERCADO PAGO
        |--------------------------------------------------------------------------
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

            /*
             * IMPORTANTE:
             *
             * Usamos el data_id ORIGINAL.
             */
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

            Log::info(
                'Mercado Pago Order consultada.',
                [
                    'order_id' => $order->id,
                    'status' => $order->status ?? null,
                    'status_detail' =>
                        $order->status_detail ?? null,
                    'total_amount' =>
                        $order->total_amount ?? null,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 9. BUSCAR SALE
            |--------------------------------------------------------------------------
            */

            $sale = Sale::query()
                ->where(
                    'mercadopago_order_id',
                    (string) $order->id
                )
                ->first();

            if (! $sale) {
                Log::warning(
                    'Mercado Pago Order sin Sale asociada.',
                    [
                        'order_id' => $order->id,
                    ]
                );

                return response()->json([
                    'message' =>
                        'Order recibida, pero no existe una Sale asociada.',
                    'order_id' =>
                        $order->id,
                ], 200);
            }

            Log::info(
                'Mercado Pago Sale encontrada.',
                [
                    'order_id' => $order->id,
                    'sale_id' => $sale->id,
                    'folio' => $sale->folio,
                    'sale_status' => $sale->status,
                    'sale_total' => $sale->total,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 10. VALIDAR ESTADO DE ORDER
            |--------------------------------------------------------------------------
            */

            $orderStatus = $order->status ?? null;

            $orderStatusDetail =
                $order->status_detail ?? null;

            if (
                $orderStatus !== 'processed' ||
                $orderStatusDetail !== 'accredited'
            ) {
                Log::info(
                    'Mercado Pago Order todavía no acreditada.',
                    [
                        'order_id' => $order->id,
                        'status' => $orderStatus,
                        'status_detail' =>
                            $orderStatusDetail,
                    ]
                );

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
            |--------------------------------------------------------------------------
            | 11. OBTENER PAYMENTS
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | 12. DATOS DEL PAYMENT
            |--------------------------------------------------------------------------
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

            Log::info(
                'Mercado Pago Payment encontrado.',
                [
                    'order_id' => $order->id,
                    'sale_id' => $sale->id,
                    'payment_id' => $paymentId,
                    'status' => $paymentStatus,
                    'status_detail' =>
                        $paymentStatusDetail,
                    'amount' => $paymentAmount,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 13. VALIDAR PAYMENT
            |--------------------------------------------------------------------------
            */

            if (
                $paymentStatus !== 'processed' ||
                $paymentStatusDetail !== 'accredited'
            ) {
                Log::info(
                    'Mercado Pago Payment todavía no acreditado.',
                    [
                        'payment_id' => $paymentId,
                        'status' => $paymentStatus,
                        'status_detail' =>
                            $paymentStatusDetail,
                    ]
                );

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
            |--------------------------------------------------------------------------
            | 14. VALIDAR MONTO
            |--------------------------------------------------------------------------
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
                Log::error(
                    'Mercado Pago: monto del pago no coincide.',
                    [
                        'sale_id' => $sale->id,
                        'payment_id' => $paymentId,
                        'payment_amount' =>
                            $paymentAmount,
                        'sale_total' =>
                            $saleTotal,
                    ]
                );

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

            Log::info(
                'Mercado Pago: monto validado.',
                [
                    'sale_id' => $sale->id,
                    'payment_id' => $paymentId,
                    'amount' => $paymentAmount,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 15. PROCESAR VENTA
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $sale,
                $paymentId,
                $paymentAmount
            ) {
                $lockedSale = Sale::query()
                    ->whereKey($sale->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedSale) {
                    throw new RuntimeException(
                        'La venta ya no existe.'
                    );
                }

                Log::info(
                    'Mercado Pago: venta bloqueada para procesamiento.',
                    [
                        'sale_id' => $lockedSale->id,
                        'folio' => $lockedSale->folio,
                        'status' => $lockedSale->status,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | 16. IDEMPOTENCIA
                |--------------------------------------------------------------------------
                |
                | Mercado Pago puede enviar el mismo Webhook más de una vez.
                |
                */

                if ($lockedSale->status === 'paid') {
                    Log::info(
                        'Mercado Pago: venta ya estaba pagada.',
                        [
                            'sale_id' => $lockedSale->id,
                            'folio' => $lockedSale->folio,
                        ]
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | 17. PAYMENT METHOD
                |--------------------------------------------------------------------------
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
                        'No existe un método de pago activo para Mercado Pago.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | 18. EVITAR PAYMENT DUPLICADO
                |--------------------------------------------------------------------------
                */

                $existingPayment = $lockedSale
                    ->payments()
                    ->where(
                        'reference',
                        (string) $paymentId
                    )
                    ->exists();

                if ($existingPayment) {
                    Log::info(
                        'Mercado Pago: Payment ya registrado.',
                        [
                            'sale_id' => $lockedSale->id,
                            'payment_id' => $paymentId,
                        ]
                    );
                } else {
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

                    Log::info(
                        'Mercado Pago: SalePayment creado.',
                        [
                            'sale_id' => $lockedSale->id,
                            'payment_id' => $paymentId,
                            'amount' => $paymentAmount,
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | 19. PRODUCTOS Y STOCK
                |--------------------------------------------------------------------------
                */

                $lockedSale->load('items');

                foreach ($lockedSale->items as $saleItem) {
                    $product = $saleItem
                        ->product()
                        ->lockForUpdate()
                        ->first();

                    if (! $product) {
                        throw new RuntimeException(
                            'El producto asociado a la venta no existe.'
                        );
                    }

                    Log::info(
                        'Mercado Pago: verificando stock.',
                        [
                            'sale_id' => $lockedSale->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'stock' => $product->stock,
                            'quantity' => $saleItem->quantity,
                        ]
                    );

                    if (
                        (int) $product->stock <
                        (int) $saleItem->quantity
                    ) {
                        throw new RuntimeException(
                            "No hay suficiente stock para el producto \"{$product->name}\"."
                        );
                    }

                    $product->decrement(
                        'stock',
                        (int) $saleItem->quantity
                    );

                    Log::info(
                        'Mercado Pago: stock descontado.',
                        [
                            'sale_id' => $lockedSale->id,
                            'product_id' => $product->id,
                            'quantity' =>
                                $saleItem->quantity,
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | 20. MARCAR VENTA COMO PAGADA
                |--------------------------------------------------------------------------
                */

                $lockedSale->update([
                    'status' => 'paid',
                    'sold_at' => now(),
                ]);

                Log::info(
                    'Mercado Pago: venta marcada como pagada.',
                    [
                        'sale_id' => $lockedSale->id,
                        'folio' => $lockedSale->folio,
                        'payment_id' => $paymentId,
                    ]
                );
            });

            /*
            |--------------------------------------------------------------------------
            | 21. RESPUESTA EXITOSA
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Mercado Pago Webhook procesado correctamente.',
                [
                    'order_id' => $order->id,
                    'payment_id' => $paymentId,
                    'sale_id' => $sale->id,
                ]
            );

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
            Log::error(
                'Mercado Pago: error al consultar la Order.',
                [
                    'data_id' => $dataId,
                    'message' => $exception->getMessage(),
                ]
            );

            return response()->json([
                'message' =>
                    'Mercado Pago rechazó la consulta de la Order.',
            ], 502);
        } catch (Throwable $exception) {
            Log::error(
                'Mercado Pago: error procesando Webhook.',
                [
                    'data_id' => $dataId,
                    'message' => $exception->getMessage(),
                    'exception' => get_class($exception),
                ]
            );

            report($exception);

            return response()->json([
                'message' =>
                    'No fue posible procesar el Webhook.',
            ], 500);
        }
    }
}
