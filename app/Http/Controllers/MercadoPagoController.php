<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    /**
     * Webhook mínimo de Mercado Pago.
     *
     * Por ahora:
     * - Recibe el webhook.
     * - Obtiene data.id.
     * - Valida x-signature.
     * - Responde 200.
     *
     * No consulta Orders ni modifica ventas/inventario.
     */
    public function webhook(Request $request): JsonResponse
    {
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        /*
        |--------------------------------------------------------------------------
        | data.id
        |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | Datos básicos
        |--------------------------------------------------------------------------
        */

        $type = $request->input('type');
        $action = $request->input('action');
        $applicationId = $request->input('application_id');

        /*
        |--------------------------------------------------------------------------
        | Validaciones básicas
        |--------------------------------------------------------------------------
        */

        if (!$dataId) {
            Log::warning('Mercado Pago: falta data.id.');

            return response()->json([
                'message' => 'Missing data.id',
            ], 400);
        }

        if (!$xSignature) {
            Log::warning('Mercado Pago: falta x-signature.', [
                'data_id' => $dataId,
            ]);

            return response()->json([
                'message' => 'Missing x-signature',
            ], 401);
        }

        if (!$xRequestId) {
            Log::warning('Mercado Pago: falta x-request-id.', [
                'data_id' => $dataId,
            ]);

            return response()->json([
                'message' => 'Missing x-request-id',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Webhook Secret
        |--------------------------------------------------------------------------
        */

        $secret = trim(
            (string) config(
                'services.mercadopago.webhook_secret'
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

        /*
        |--------------------------------------------------------------------------
        | Parsear x-signature
        |--------------------------------------------------------------------------
        */

        $ts = null;
        $v1 = null;

        foreach (explode(',', $xSignature) as $part) {
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
                    'x_signature' => $xSignature,
                ]
            );

            return response()->json([
                'message' => 'Invalid x-signature',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Manifest
        |--------------------------------------------------------------------------
        */

        $manifest = sprintf(
            'id:%s;request-id:%s;ts:%s;',
            strtolower($dataId),
            $xRequestId,
            $ts
        );

        /*
        |--------------------------------------------------------------------------
        | HMAC
        |--------------------------------------------------------------------------
        */

        $calculatedSignature = hash_hmac(
            'sha256',
            $manifest,
            $secret
        );

        /*
        |--------------------------------------------------------------------------
        | Log mínimo
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Mercado Pago Webhook HMAC',
            [
                'data_id' => $dataId,
                'type' => $type,
                'action' => $action,
                'application_id' => $applicationId,
                'x_request_id' => $xRequestId,
                'ts' => $ts,
                'received_signature' => $v1,
                'calculated_signature' => $calculatedSignature,
                'signatures_match' => hash_equals(
                    $calculatedSignature,
                    $v1
                ),
            ]
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
        | TODO
        |--------------------------------------------------------------------------
        |
        | Cuando confirmemos que el HMAC funciona:
        |
        | 1. Consultar Order
        | 2. Validar status
        | 3. Validar payment
        | 4. Validar monto
        | 5. Buscar Sale
        | 6. Descontar inventario
        | 7. Registrar payment
        | 8. Marcar venta como pagada
        |
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Mercado Pago Webhook recibido correctamente.',
            [
                'data_id' => $dataId,
            ]
        );

        return response()->json([
            'message' => 'Webhook received',
        ], 200);
    }
}