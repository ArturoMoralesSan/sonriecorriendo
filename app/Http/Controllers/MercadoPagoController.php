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
        $xSignature = $request->headers->get('x-signature');
        $xRequestId = $request->headers->get('x-request-id');

        // Mercado Pago envía la clave literal "data.id" en el query string.
        $dataId = $request->query('data.id');
        $dataId = strtolower($dataId);

        $secret = trim(
            (string) config('services.mercadopago.webhook_secret')
        );

        try {
            WebhookSignatureValidator::validate(
                $xSignature,
                $xRequestId,
                $dataId,
                $secret
            );

            return response()->json([
                'message' => 'Webhook válido',
            ], 200);

        } catch (InvalidWebhookSignatureException $e) {
            Log::warning('Mercado Pago Webhook firma inválida.', [
                'data_id' => $dataId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Invalid webhook signature',
            ], 401);
        } catch (Throwable $e) {
            Log::error('Mercado Pago Webhook error.', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Webhook error',
            ], 500);
        }
    }
}