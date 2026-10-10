<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmationMail;
use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Throwable;

class MercadoPagoController extends Controller
{
    public function webhook(Request $request): JsonResponse
    {
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        /*
        |--------------------------------------------------------------------------
        | Obtener data.id
        |--------------------------------------------------------------------------
        */

        $dataId = $request->query('data.id');

        if ($dataId === null || $dataId === '') {
            $dataId = $request->query('data_id');
        }

        if ($dataId === null || $dataId === '') {
            $dataId = data_get($request->input('data'), 'id');
        }

        $dataId = $dataId !== null
            ? trim((string) $dataId)
            : null;

        $type = $request->input('type');

        /*
        |--------------------------------------------------------------------------
        | Validaciones básicas
        |--------------------------------------------------------------------------
        */

        if (!$dataId) {
            return response()->json([
                'message' => 'Missing data.id',
            ], 400);
        }

        if (!$xSignature) {
            return response()->json([
                'message' => 'Missing x-signature',
            ], 401);
        }

        if (!$xRequestId) {
            return response()->json([
                'message' => 'Missing x-request-id',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Solo procesar eventos Order
        |--------------------------------------------------------------------------
        */

        if ($type !== 'order') {
            return response()->json([
                'message' => 'Event ignored',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Webhook Secret
        |--------------------------------------------------------------------------
        */

        $secret = trim(
            (string) config('services.mercadopago.webhook_secret')
        );

        if ($secret === '') {
            Log::error(
                'Mercado Pago: Webhook Secret no configurado.'
            );

            return response()->json([
                'message' => 'Webhook secret not configured',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | Parsear x-signature
        |--------------------------------------------------------------------------
        */

        $ts = null;
        $v1 = null;

        foreach (explode(',', $xSignature) as $part) {
            $keyValue = explode('=', trim($part), 2);

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
            return response()->json([
                'message' => 'Invalid x-signature',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar firma HMAC de Mercado Pago
        |--------------------------------------------------------------------------
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

        if (!hash_equals($calculatedSignature, $v1)) {
            Log::warning(
                'Mercado Pago: firma HMAC inválida.',
                [
                    'data_id' => $dataId,
                ]
            );

            return response()->json([
                'message' => 'Invalid webhook signature',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Access Token
        |--------------------------------------------------------------------------
        */

        $accessToken = trim(
            (string) config('services.mercadopago.access_token')
        );

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
            | Consultar Order en Mercado Pago
            |--------------------------------------------------------------------------
            */

            MercadoPagoConfig::setAccessToken($accessToken);

            $client = new OrderClient();

            $order = $client->get($dataId);

            if (!$order) {
                return response()->json([
                    'message' => 'Order not found',
                ], 404);
            }

            $orderId = (string) ($order->id ?? $dataId);
            $orderStatus = $order->status ?? null;
            $orderStatusDetail = $order->status_detail ?? null;

            /*
            |--------------------------------------------------------------------------
            | Buscar venta relacionada
            |--------------------------------------------------------------------------
            */

            $sale = Sale::where(
                'mercadopago_order_id',
                $orderId
            )->first();

            if (!$sale) {
                return response()->json([
                    'message' => 'Sale not found',
                ], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar Order
            |--------------------------------------------------------------------------
            */

            if ($orderStatus !== 'processed') {
                return response()->json([
                    'message' => 'Order not processed',
                ], 200);
            }

            if ($orderStatusDetail !== 'accredited') {
                return response()->json([
                    'message' => 'Order not accredited',
                ], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Obtener pagos
            |--------------------------------------------------------------------------
            */

            $payments = $order->transactions->payments ?? [];

            if (empty($payments)) {
                return response()->json([
                    'message' => 'No payments',
                ], 200);
            }

            $payment = $payments[0];

            $paymentId = isset($payment->id)
                ? (string) $payment->id
                : null;

            $paymentAmount = isset($payment->amount)
                ? (float) $payment->amount
                : 0;

            $paymentStatus = $payment->status ?? null;

            $paymentStatusDetail = $payment->status_detail ?? null;

            /*
            |--------------------------------------------------------------------------
            | Validar Payment
            |--------------------------------------------------------------------------
            */

            if (!$paymentId) {
                return response()->json([
                    'message' => 'Payment ID missing',
                ], 200);
            }

            if ($paymentStatus !== 'processed') {
                return response()->json([
                    'message' => 'Payment not processed',
                ], 200);
            }

            if ($paymentStatusDetail !== 'accredited') {
                return response()->json([
                    'message' => 'Payment not accredited',
                ], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar monto pagado
            |--------------------------------------------------------------------------
            */

            $saleTotal = (float) $sale->total;

            if (abs($paymentAmount - $saleTotal) > 0.01) {
                Log::error(
                    'Mercado Pago: monto incorrecto.',
                    [
                        'order_id' => $orderId,
                        'payment_id' => $paymentId,
                        'sale_id' => $sale->id,
                        'sale_total' => $saleTotal,
                        'payment_amount' => $paymentAmount,
                    ]
                );

                return response()->json([
                    'message' => 'Payment amount mismatch',
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Procesar pago y actualizar inventario
            |--------------------------------------------------------------------------
            |
            | La transacción devuelve true solamente cuando esta ejecución
            | registra el pago y marca la venta como pagada.
            |
            */

            $shouldSendConfirmation = DB::transaction(
                function () use (
                    $sale,
                    $orderId,
                    $paymentId,
                    $paymentAmount
                ): bool {
                    $lockedSale = Sale::where('id', $sale->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$lockedSale) {
                        throw new \RuntimeException(
                            'Sale not found while locking.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Evitar procesar dos veces la misma venta
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array(
                            $lockedSale->status,
                            ['paid', 'pagada'],
                            true
                        )
                    ) {
                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Obtener método de pago
                    |--------------------------------------------------------------------------
                    */

                    $paymentMethod = PaymentMethod::where(
                        'code',
                        'mercadopago'
                    )
                        ->where('is_active', true)
                        ->first();

                    if (!$paymentMethod) {
                        throw new \RuntimeException(
                            'Payment method mercadopago not found or inactive.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Evitar registrar el mismo pago dos veces
                    |--------------------------------------------------------------------------
                    */

                    $existingPayment = $lockedSale
                        ->payments()
                        ->where('reference', $paymentId)
                        ->exists();

                    if ($existingPayment) {
                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Obtener productos de la venta
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

                        $quantity = (float) $item->quantity;

                        $lockedProduct = $product
                            ->newQuery()
                            ->where('id', $product->id)
                            ->lockForUpdate()
                            ->first();

                        if (!$lockedProduct) {
                            throw new \RuntimeException(
                                "Product {$product->id} not found."
                            );
                        }

                        if ((float) $lockedProduct->stock < $quantity) {
                            throw new \RuntimeException(
                                "Insufficient stock for product {$lockedProduct->id}."
                            );
                        }

                        $lockedProduct->stock =
                            (float) $lockedProduct->stock - $quantity;

                        $lockedProduct->save();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Registrar el pago
                    |--------------------------------------------------------------------------
                    */

                    $lockedSale->payments()->create([
                        'payment_method_id' => $paymentMethod->id,
                        'amount' => $paymentAmount,
                        'reference' => $paymentId,
                        'notes' => "Mercado Pago Order {$orderId}",
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Marcar la venta como pagada
                    |--------------------------------------------------------------------------
                    */

                    $lockedSale->status = 'paid';
                    $lockedSale->sold_at = now();
                    $lockedSale->save();

                    return true;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Enviar correo de confirmación
            |--------------------------------------------------------------------------
            |
            | Solo se envía cuando esta ejecución acaba de procesar el pago.
            | El envío se hace fuera de la transacción para que un error de
            | correo no revierta el pago ni el descuento de inventario.
            |
            */

            if ($shouldSendConfirmation) {
                try {
                    $sale->refresh();

                    $sale->load([
                        'customer',
                        'items.product',
                        'deliveryAddress.branch',
                    ]);

                    Mail::to($sale->customer_email)->send(
                        new OrderConfirmationMail($sale)
                    );

                    Log::info(
                        'Correo de confirmación enviado correctamente.',
                        [
                            'sale_id' => $sale->id,
                            'folio' => $sale->folio,
                            'email' => $sale->customer_email,
                        ]
                    );
                } catch (Throwable $mailException) {
                    /*
                    |--------------------------------------------------------------------------
                    | Un error de correo no debe invalidar el pago
                    |--------------------------------------------------------------------------
                    */

                    Log::error(
                        'No se pudo enviar el correo de confirmación.',
                        [
                            'sale_id' => $sale->id,
                            'folio' => $sale->folio,
                            'email' => $sale->customer_email,
                            'message' => $mailException->getMessage(),
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Log de procesamiento
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Mercado Pago Webhook procesado correctamente.',
                [
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                    'sale_id' => $sale->id,
                    'confirmation_email_attempted' => $shouldSendConfirmation,
                ]
            );

            return response()->json([
                'message' => 'Webhook processed successfully',
            ], 200);

        } catch (MPApiException $e) {
            Log::error(
                'Mercado Pago: error API.',
                [
                    'data_id' => $dataId,
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'message' => 'Mercado Pago API error',
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
                'message' => 'Webhook processing error',
            ], 500);
        }
    }
}