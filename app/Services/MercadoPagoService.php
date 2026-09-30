<?php

namespace App\Services;

use Illuminate\Support\Str;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use RuntimeException;

class MercadoPagoService
{
    private OrderClient $orderClient;

    public function __construct()
    {
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

        $this->orderClient = new OrderClient();
    }

    /**
     * Crea una Order de Mercado Pago para Checkout Pro.
     *
     * @param array<int, array<string, mixed>> $items
     */
    public function createOrder(
        string $externalReference,
        string $description,
        float $total,
        array $items,
        ?string $payerEmail = null,
        ?string $successUrl = null,
        ?string $failureUrl = null,
        ?string $pendingUrl = null,
    ): object {
        if ($total <= 0) {
            throw new RuntimeException(
                'El total de la Order debe ser mayor a cero.'
            );
        }

        if (empty($items)) {
            throw new RuntimeException(
                'La Order debe contener al menos un producto.'
            );
        }

        $request = [
            'type' => 'online',

            'processing_mode' => 'manual',

            'total_amount' => number_format(
                $total,
                2,
                '.',
                ''
            ),

            'external_reference' => $externalReference,

            'description' => $description,

            'items' => $items,
        ];

        if ($payerEmail) {
            $request['payer'] = [
                'email' => $payerEmail,
            ];
        }

        if (
            $successUrl ||
            $failureUrl ||
            $pendingUrl
        ) {
            $request['config'] = [
                'online' => array_filter([
                    'success_url' => $successUrl,
                    'failure_url' => $failureUrl,
                    'pending_url' => $pendingUrl,
                    'auto_return' => 'approved',
                ]),
            ];
        }

        $requestOptions = new RequestOptions();

        $requestOptions->setCustomHeaders([
            'X-Idempotency-Key: ' . Str::uuid()->toString(),
        ]);

        try {
            return $this->orderClient->create(
                $request,
                $requestOptions
            );
        } catch (MPApiException $exception) {
            $content = $exception
                ->getApiResponse()
                ->getContent();

            $message = is_string($content)
                ? $content
                : json_encode(
                    $content,
                    JSON_UNESCAPED_UNICODE
                );

            throw new RuntimeException(
                'Mercado Pago rechazó la creación de la Order. ' .
                $message
            );
        }
    }
}