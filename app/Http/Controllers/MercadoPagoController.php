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
        | Headers de Mercado Pago
        |--------------------------------------------------------------------------
        */

        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        /*
        |--------------------------------------------------------------------------
        | data.id
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | Para la firma debemos utilizar específicamente el parámetro
        | data.id de la URL.
        |
        | Ejemplo:
        |
        | ?data.id=ORDTST01M3TXZPDK7802MSPF2DWTKX6P&type=order
        |
        */

        $dataId = $request->query('data.id');

        if ($dataId === null || $dataId === '') {
            $dataId = $request->query('data_id');
        }

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

        /*
        |--------------------------------------------------------------------------
        | Log inicial
        |--------------------------------------------------------------------------
        */

        Log::info('Mercado Pago Webhook recibido', [
            'full_url' => $request->fullUrl(),
            'data_id' => $dataId,
            'type' => $type,
            'action' => $action,
            'live_mode' => $liveMode,
            'application_id' => $applicationId,
            'x_request_id' => $xRequestId,
            'has_signature' => !empty($xSignature),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Configuración
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

            return response()->json([
                'message' => 'Webhook secret not configured',
            ], 500);
        }

        if ($dataId === null || $dataId === '') {
            Log::warning(
                'Mercado Pago: webhook sin data.id.'
            );

            return response()->json([
                'message' => 'Missing data.id',
            ], 400);
        }

        if (!$xSignature) {
            Log::warning(
                'Mercado Pago: falta x-signature.',
                [
                    'data_id' => $dataId,
                ]
            );

            return response()->json([
                'message' => 'Missing x-signature',
            ], 401);
        }

        if (!$xRequestId) {
            Log::warning(
                'Mercado Pago: falta x-request-id.',
                [
                    'data_id' => $dataId,
                ]
            );

            return response()->json([
                'message' => 'Missing x-request-id',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Solo procesamos Order
        |--------------------------------------------------------------------------
        */

        if ($type !== 'order') {
            Log::info(
                'Mercado Pago Webhook ignorado.',
                [
                    'data_id' => $dataId,
                    'type' => $type,
                    'action' => $action,
                ]
            );

            return response()->json([
                'message' => 'Event ignored',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Parsear x-signature
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | ts=1742505638683,v1=ced36ab...
        |
        */

        $ts = null;
        $v1 = null;

        foreach (
            explode(',', $xSignature) as $part
        ) {
            $keyValue = explode(
                '=',
                trim($part),
                2
            );

            if (count($keyValue) !== 2) {
                continue;
            }

            $key = trim($keyValue[0]);
            $value = trim($keyValue[1]);

            if ($key === 'ts') {
                $ts = $value;
            }

            if ($key === 'v1') {
                $v1 = $value;
            }
        }

        if (!$ts || !$v1) {
            Log::warning(
                'Mercado Pago: x-signature inválida.',
                [
                    'data_id' => $dataId,
                ]
            );

            return response()->json([
                'message' => 'Invalid x-signature',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validación HMAC oficial de Mercado Pago
        |--------------------------------------------------------------------------
        |
        | Manifest oficial:
        |
        | id:[data.id];request-id:[x-request-id];ts:[ts];
        |
        | IMPORTANTE:
        |
        | Mercado Pago indica que data.id debe utilizarse en minúsculas
        | para la validación.
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
        | Comparar firma
        |--------------------------------------------------------------------------
        */

        if (!hash_equals(
            $calculatedSignature,
            $v1
        )) {
            Log::warning(
                'Mercado Pago: firma HMAC inválida.',
                [
                    'data_id' => $dataId,
                    'request_id' => $xRequestId,
                    'ts' => $ts,
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

            return response()->json([
                'message' => 'Invalid webhook signature',
            ], 401);
        }

        Log::info(
            'Mercado Pago: firma HMAC validada.',
            [
                'data_id' => $dataId,
                'request_id' => $xRequestId,
                'ts' => $ts,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Access Token
        |--------------------------------------------------------------------------
        */

        if ($accessToken === '') {
            Log::error(
                'Mercado Pago: Access Token no configurado.'
            );

            return response()->json([
                'message' => 'Access token not configured',
            ], 500);
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Configurar SDK
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
                    'Mercado Pago: Order no encontrada.',
                    [
                        'data_id' => $dataId,
                    ]
                );

                return response()->json([
                    'message' => 'Order not found',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Datos de Order
            |--------------------------------------------------------------------------
            */

            $orderId = (string) (
                $order->id ?? $dataId
            );

            $orderStatus = $order->status ?? null;

            $orderStatusDetail =
                $order->status_detail ?? null;

            Log::info(
                'Mercado Pago: Order consultada.',
                [
                    'order_id' => $orderId,
                    'status' => $orderStatus,
                    'status_detail' => $orderStatusDetail,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Buscar Sale
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

                /*
                |--------------------------------------------------------------------------
                | Respondemos 200
                |--------------------------------------------------------------------------
                |
                | La notificación fue recibida y validada correctamente,
                | pero no existe una venta local asociada.
                |
                */

                return response()->json([
                    'message' => 'Sale not found',
                ], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar estado de Order
            |--------------------------------------------------------------------------
            */

            if ($orderStatus !== 'processed') {
                Log::info(
                    'Mercado Pago: Order todavía no procesada.',
                    [
                        'order_id' => $orderId,
                        'status' => $orderStatus,
                        'status_detail' => $orderStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json([
                    'message' => 'Order not processed',
                ], 200);
            }

            if ($orderStatusDetail !== 'accredited') {
                Log::info(
                    'Mercado Pago: Order no acreditada.',
                    [
                        'order_id' => $orderId,
                        'status' => $orderStatus,
                        'status_detail' => $orderStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json([
                    'message' => 'Order not accredited',
                ], 200);
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

                return response()->json([
                    'message' => 'No payments',
                ], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

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
            | Validar Payment
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

                return response()->json([
                    'message' => 'Payment ID missing',
                ], 200);
            }

            if ($paymentStatus !== 'processed') {
                Log::info(
                    'Mercado Pago: payment todavía no procesado.',
                    [
                        'order_id' => $orderId,
                        'payment_id' => $paymentId,
                        'status' => $paymentStatus,
                        'status_detail' => $paymentStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json([
                    'message' => 'Payment not processed',
                ], 200);
            }

            if ($paymentStatusDetail !== 'accredited') {
                Log::info(
                    'Mercado Pago: payment no acreditado.',
                    [
                        'order_id' => $orderId,
                        'payment_id' => $paymentId,
                        'status' => $paymentStatus,
                        'status_detail' => $paymentStatusDetail,
                        'sale_id' => $sale->id,
                    ]
                );

                return response()->json([
                    'message' => 'Payment not accredited',
                ], 200);
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

                return response()->json([
                    'message' => 'Payment amount mismatch',
                ], 400);
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
                    | Bloquear Sale
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
                    | Obtener items
                    |--------------------------------------------------------------------------
                    */

                    $items = $lockedSale
                        ->items()
                        ->with('product')
                        ->get();

                    /*
                    |--------------------------------------------------------------------------
                    | Descontar inventario
                    |--------------------------------------------------------------------------
                    */

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
                    | Registrar Payment
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
                    | Marcar Sale como pagada
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

            return response()->json([
                'message' =>
                    'Webhook processed successfully',
            ], 200);

        } catch (MPApiException $e) {
            Log::error(
                'Mercado Pago: error al consultar la Order.',
                [
                    'data_id' => $dataId,
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'message' =>
                    'Mercado Pago API error',
            ], 502);

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

            return response()->json([
                'message' =>
                    'Webhook processing error',
            ], 500);
        }
    }
}
