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
use MercadoPago\MercadoPagoConfig;
use Throwable;

class MercadoPagoController extends Controller
{
    /**
     * Webhook de Mercado Pago.
     */
    public function webhook(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Datos del webhook
        |--------------------------------------------------------------------------
        */

        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        /*
         * Mercado Pago documenta el parámetro como:
         *
         * ?data.id=ORDTST...
         *
         * PHP/Laravel normalmente lo recibe como:
         *
         * data_id
         *
         * Por eso data_id es nuestra fuente principal.
         */

        $dataId = $request->query('data_id');

        /*
         * Respaldo por si el framework conserva data.id literalmente.
         */
        if ($dataId === null || $dataId === '') {
            $dataId = $request->query('data.id');
        }

        /*
         * Respaldo adicional por si viene dentro del JSON.
         */
        if ($dataId === null || $dataId === '') {
            $dataId = data_get(
                $request->input('data'),
                'id'
            );
        }

        $dataId = $dataId !== null
            ? trim((string) $dataId)
            : null;

        $type = $request->input('type');
        $action = $request->input('action');
        $liveMode = $request->boolean('live_mode');
        $applicationId = $request->input('application_id');

        Log::info(
            'Mercado Pago Webhook recibido',
            [
                'data_id' => $dataId,
                'type' => $type,
                'action' => $action,
                'live_mode' => $liveMode,
                'application_id' => $applicationId,
                'has_signature' => !empty($xSignature),
                'has_request_id' => !empty($xRequestId),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Credenciales
        |--------------------------------------------------------------------------
        */

        $secret = trim(
            (string) config(
                'services.mercadopago.webhook_secret'
            )
        );

        $accessToken = trim(
            (string) config(
                'services.mercadopago.access_token'
            )
        );

        if ($secret === '') {
            Log::error(
                'Mercado Pago: Webhook Secret no configurado.'
            );

            return response()->json(
                [
                    'message' => 'Webhook secret not configured',
                ],
                500
            );
        }

        if ($dataId === null || $dataId === '') {
            Log::warning(
                'Mercado Pago: webhook sin data_id.'
            );

            return response()->json(
                [
                    'message' => 'Missing data_id',
                ],
                400
            );
        }

        if (!$xSignature || !$xRequestId) {
            Log::warning(
                'Mercado Pago: faltan headers de firma.',
                [
                    'data_id' => $dataId,
                    'has_signature' => !empty($xSignature),
                    'has_request_id' => !empty($xRequestId),
                ]
            );

            return response()->json(
                [
                    'message' => 'Missing signature headers',
                ],
                401
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validación HMAC-SHA256
        |--------------------------------------------------------------------------
        |
        | x-signature tiene una estructura similar a:
        |
        | ts=123456789,v1=abcdef...
        |
        */

        $signatureParts = [];

        foreach (
            explode(
                ',',
                (string) $xSignature
            ) as $part
        ) {
            [$key, $value] = array_pad(
                explode(
                    '=',
                    trim($part),
                    2
                ),
                2,
                null
            );

            if (
                $key !== null &&
                $value !== null
            ) {
                $signatureParts[
                    strtolower(trim($key))
                ] = trim($value);
            }
        }

        $ts = $signatureParts['ts'] ?? null;
        $v1 = $signatureParts['v1'] ?? null;

        if (!$ts || !$v1) {
            Log::warning(
                'Mercado Pago: x-signature no contiene ts/v1.',
                [
                    'data_id' => $dataId,
                ]
            );

            return response()->json(
                [
                    'message' => 'Invalid signature format',
                ],
                401
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Manifest de Mercado Pago
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | Para IDs alfanuméricos de Order utilizamos data_id
        | en minúsculas.
        |
        | Formato:
        |
        | id:<data_id>;
        | request-id:<x-request-id>;
        | ts:<timestamp>;
        |
        */

        $manifest = sprintf(
            'id:%s;request-id:%s;ts:%s;',
            strtolower($dataId),
            $xRequestId,
            $ts
        );

        $calculatedSignature = hash_hmac(
            'sha256',
            $manifest,
            $secret
        );

        /*
        |--------------------------------------------------------------------------
        | Validar firma
        |--------------------------------------------------------------------------
        */

        if (!hash_equals(
            $calculatedSignature,
            $v1
        )) {
            Log::warning(
                'Mercado Pago Webhook firma inválida.',
                [
                    'data_id' => $dataId,
                    'live_mode' => $liveMode,
                    'application_id' => $applicationId,

                    /*
                     * Solo mostramos prefijos para poder
                     * diagnosticar sin exponer secretos.
                     */
                    'received_signature_prefix' => substr(
                        $v1,
                        0,
                        8
                    ),

                    'calculated_signature_prefix' => substr(
                        $calculatedSignature,
                        0,
                        8
                    ),
                ]
            );

            return response()->json(
                [
                    'message' => 'Invalid webhook signature',
                ],
                401
            );
        }

        Log::info(
            'Mercado Pago Webhook firma validada.',
            [
                'data_id' => $dataId,
                'live_mode' => $liveMode,
                'application_id' => $applicationId,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Solo procesamos eventos Order
        |--------------------------------------------------------------------------
        */

        if ($type !== 'order') {
            Log::info(
                'Mercado Pago Webhook ignorado: tipo no soportado.',
                [
                    'data_id' => $dataId,
                    'type' => $type,
                    'action' => $action,
                ]
            );

            return response()->json(
                [
                    'message' => 'Event ignored',
                ],
                200
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Access Token
        |--------------------------------------------------------------------------
        */

        if ($accessToken === '') {
            Log::error(
                'Mercado Pago: Access Token no configurado.'
            );

            return response()->json(
                [
                    'message' => 'Access token not configured',
                ],
                500
            );
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Configuración SDK
            |--------------------------------------------------------------------------
            */

            MercadoPagoConfig::setAccessToken(
                $accessToken
            );

            /*
            |--------------------------------------------------------------------------
            | Consultar Order
            |--------------------------------------------------------------------------
            */

            $client = new OrderClient();

            $order = $client->get($dataId);

            if (!$order) {
                Log::error(
                    'Mercado Pago: no se encontró la Order.',
                    [
                        'data_id' => $dataId,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Order not found',
                    ],
                    404
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Datos principales de la Order
            |--------------------------------------------------------------------------
            */

            $orderId = (string) (
                $order->id ?? $dataId
            );

            $orderStatus = $order->status ?? null;

            $orderStatusDetail =
                $order->status_detail ?? null;

            Log::info(
                'Mercado Pago Order consultada.',
                [
                    'order_id' => $orderId,
                    'status' => $orderStatus,
                    'status_detail' => $orderStatusDetail,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Buscar venta
            |--------------------------------------------------------------------------
            */

            $sale = Sale::where(
                'mercadopago_order_id',
                $orderId
            )->first();

            if (!$sale) {
                Log::warning(
                    'Mercado Pago: venta no encontrada.',
                    [
                        'order_id' => $orderId,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Sale not found',
                    ],
                    200
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validar Order
            |--------------------------------------------------------------------------
            */

            if ($orderStatus !== 'processed') {
                Log::warning(
                    'Mercado Pago: Order todavía no está procesada.',
                    [
                        'order_id' => $orderId,
                        'status' => $orderStatus,
                        'status_detail' => $orderStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Order not processed',
                    ],
                    200
                );
            }

            if ($orderStatusDetail !== 'accredited') {
                Log::warning(
                    'Mercado Pago: Order no está acreditada.',
                    [
                        'order_id' => $orderId,
                        'status' => $orderStatus,
                        'status_detail' => $orderStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Order not accredited',
                    ],
                    200
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Obtener payments
            |--------------------------------------------------------------------------
            */

            $payments =
                $order->transactions->payments ?? [];

            if (empty($payments)) {
                Log::warning(
                    'Mercado Pago: Order sin payments.',
                    [
                        'order_id' => $orderId,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'No payments',
                    ],
                    200
                );
            }

            $payment = $payments[0];

            $paymentId = isset($payment->id)
                ? (string) $payment->id
                : null;

            $paymentAmount = isset($payment->amount)
                ? (float) $payment->amount
                : 0;

            $paymentStatus =
                $payment->status ?? null;

            $paymentStatusDetail =
                $payment->status_detail ?? null;

            /*
            |--------------------------------------------------------------------------
            | Validar payment
            |--------------------------------------------------------------------------
            */

            if (!$paymentId) {
                Log::warning(
                    'Mercado Pago: payment sin ID.',
                    [
                        'order_id' => $orderId,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Payment ID missing',
                    ],
                    200
                );
            }

            if ($paymentStatus !== 'processed') {
                Log::warning(
                    'Mercado Pago: payment no procesado.',
                    [
                        'order_id' => $orderId,
                        'payment_id' => $paymentId,
                        'status' => $paymentStatus,
                        'status_detail' => $paymentStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Payment not processed',
                    ],
                    200
                );
            }

            if ($paymentStatusDetail !== 'accredited') {
                Log::warning(
                    'Mercado Pago: payment no acreditado.',
                    [
                        'order_id' => $orderId,
                        'payment_id' => $paymentId,
                        'status' => $paymentStatus,
                        'status_detail' => $paymentStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Payment not accredited',
                    ],
                    200
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validar monto
            |--------------------------------------------------------------------------
            */

            $saleTotal = (float) $sale->total;

            if (
                abs(
                    $paymentAmount - $saleTotal
                ) > 0.01
            ) {
                Log::error(
                    'Mercado Pago: monto incorrecto.',
                    [
                        'order_id' => $orderId,
                        'payment_id' => $paymentId,
                        'payment_amount' => $paymentAmount,
                        'sale_total' => $saleTotal,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json(
                    [
                        'message' => 'Payment amount mismatch',
                    ],
                    400
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Procesar venta
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $sale,
                    $orderId,
                    $paymentId,
                    $paymentAmount
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Bloquear venta
                    |--------------------------------------------------------------------------
                    */

                    $lockedSale = Sale::where(
                        'id',
                        $sale->id
                    )
                        ->lockForUpdate()
                        ->first();

                    if (!$lockedSale) {
                        throw new \RuntimeException(
                            'Sale not found while locking.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Evitar doble procesamiento
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isset($lockedSale->status) &&
                        in_array(
                            $lockedSale->status,
                            [
                                'paid',
                                'pagada',
                            ],
                            true
                        )
                    ) {
                        Log::info(
                            'Mercado Pago: venta ya estaba pagada.',
                            [
                                'sale_id' => $lockedSale->id,
                                'order_id' => $orderId,
                                'payment_id' => $paymentId,
                            ]
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Payment Method
                    |--------------------------------------------------------------------------
                    */

                    $paymentMethod =
                        PaymentMethod::where(
                            'code',
                            'mercadopago'
                        )
                            ->where(
                                'is_active',
                                true
                            )
                            ->first();

                    if (!$paymentMethod) {
                        throw new \RuntimeException(
                            'Payment method mercadopago not found or inactive.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Evitar payment duplicado
                    |--------------------------------------------------------------------------
                    */

                    $existingPayment =
                        $lockedSale
                            ->payments()
                            ->where(
                                'reference',
                                $paymentId
                            )
                            ->exists();

                    if ($existingPayment) {
                        Log::info(
                            'Mercado Pago: payment ya registrado.',
                            [
                                'sale_id' => $lockedSale->id,
                                'order_id' => $orderId,
                                'payment_id' => $paymentId,
                            ]
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Descontar inventario
                    |--------------------------------------------------------------------------
                    */

                    $items = $lockedSale
                        ->items()
                        ->with('product')
                        ->get();

                    foreach ($items as $item) {
                        $product = $item->product;

                        if (!$product) {
                            throw new \RuntimeException(
                                "Product not found for sale item {$item->id}."
                            );
                        }

                        $quantity =
                            (float) $item->quantity;

                        $lockedProduct = $product
                            ->newQuery()
                            ->where(
                                'id',
                                $product->id
                            )
                            ->lockForUpdate()
                            ->first();

                        if (!$lockedProduct) {
                            throw new \RuntimeException(
                                "Product {$product->id} not found."
                            );
                        }

                        if (
                            (float) $lockedProduct->stock
                            < $quantity
                        ) {
                            throw new \RuntimeException(
                                "Insufficient stock for product {$lockedProduct->id}."
                            );
                        }

                        $lockedProduct->stock =
                            (float) $lockedProduct->stock
                            - $quantity;

                        $lockedProduct->save();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Registrar payment
                    |--------------------------------------------------------------------------
                    */

                    $lockedSale
                        ->payments()
                        ->create([
                            'payment_method_id' =>
                                $paymentMethod->id,

                            'amount' =>
                                $paymentAmount,

                            'reference' =>
                                $paymentId,

                            'notes' =>
                                "Mercado Pago Order {$orderId}",
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Marcar venta como pagada
                    |--------------------------------------------------------------------------
                    */

                    $lockedSale->status = 'paid';
                    $lockedSale->sold_at = now();
                    $lockedSale->save();
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Éxito
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Mercado Pago Webhook procesado correctamente.',
                [
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                    'sale_id' => $sale->id,
                ]
            );

            return response()->json(
                [
                    'message' =>
                        'Webhook processed successfully',
                ],
                200
            );
        } catch (MPApiException $e) {
            Log::error(
                'Mercado Pago: error al consultar la Order.',
                [
                    'data_id' => $dataId,
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json(
                [
                    'message' =>
                        'Mercado Pago API error',
                ],
                502
            );
        } catch (Throwable $e) {
            Log::error(
                'Mercado Pago: error procesando webhook.',
                [
                    'data_id' => $dataId,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return response()->json(
                [
                    'message' =>
                        'Webhook processing error',
                ],
                500
            );
        }
    }
}