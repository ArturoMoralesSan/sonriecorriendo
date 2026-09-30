<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\Webhook\WebhookSignatureValidator;

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
         * Por ahora solamente confirmamos que la notificación
         * fue recibida y validada correctamente.
         *
         * En el siguiente paso:
         *
         * 1. Identificaremos la Order.
         * 2. Consultaremos su estado en Mercado Pago.
         * 3. Buscaremos nuestra Sale mediante external_reference.
         * 4. Crearemos SalePayment.
         * 5. Cambiaremos la venta a paid.
         * 6. Descontaremos el stock.
         */

        return response()->json([
            'message' => 'Webhook recibido correctamente.',
        ], 200);
    }
}