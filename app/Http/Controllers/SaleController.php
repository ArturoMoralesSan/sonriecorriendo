<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class SaleController extends Controller
{
    public function checkout(
        Request $request,
        MercadoPagoService $mercadoPago
    ): JsonResponse {
        try {
            /**
             * Primero creamos nuestra venta y sus productos.
             *
             * Todavía queda como pending.
             * No se descuenta stock.
             */
            $sale = DB::transaction(function () use ($request) {
                return $this->createSaleFromCart($request);
            });

            /**
             * Preparamos los productos para Mercado Pago.
             */
            $sale->load('items.product');

            $items = $sale->items
                ->map(function ($item) {
                    return [
                        'title' => $item->product->name,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => number_format(
                            (float) $item->unit_price,
                            2,
                            '.',
                            ''
                        ),
                    ];
                })
                ->values()
                ->all();

            /**
             * URLs a las que Mercado Pago regresará al comprador.
             *
             * El pago NO se confirma aquí.
             */
            $successUrl = route(
                'sales.show',
                $sale
            );

            $failureUrl = route(
                'sales.show',
                $sale
            );

            $pendingUrl = route(
                'sales.show',
                $sale
            );

            /**
             * Creamos la Order en Mercado Pago.
             */
            $order = $mercadoPago->createOrder(
                externalReference: $sale->folio,
                description: 'Pedido Sonríe Corriendo ' . $sale->folio,
                total: (float) $sale->total,
                items: $items,
                payerEmail: $request->user()?->email,
                successUrl: $successUrl,
                failureUrl: $failureUrl,
                pendingUrl: $pendingUrl,
            );

            /**
             * Mercado Pago debe devolver un ID de Order.
             */
            if (empty($order->id)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió el ID de la Order.'
                );
            }

            /**
             * Mercado Pago debe devolver la URL de Checkout.
             */
            if (empty($order->checkout_url)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió la URL de Checkout.'
                );
            }

            /**
             * Guardamos la relación entre nuestra venta
             * y la Order de Mercado Pago.
             */
            $sale->update([
                'mercadopago_order_id' => $order->id,
            ]);

            /**
             * El carrito solamente se vacía después de
             * crear correctamente la Order.
             */
            $request->session()->forget('cart');

            /**
             * IMPORTANTE:
             * No hacemos redirect()->away() aquí.
             *
             * Cart.vue recibe esta respuesta mediante fetch()
             * y posteriormente hace:
             *
             * window.location.href = checkout_url;
             */
            return response()->json([
                'checkout_url' => $order->checkout_url,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'No fue posible iniciar el checkout.',
                'errors' => [
                    'sale' => [
                        $exception->getMessage(),
                    ],
                ],
            ], 422);
        }
    }

    public function show(
        Request $request,
        Sale $sale
    ): Response {
        if (
            $sale->customer_id !== null &&
            $request->user()?->id !== $sale->customer_id
        ) {
            abort(403);
        }

        $sale->load([
            'customer',
            'items.product',
        ]);

        return Inertia::render('SaleShow', [
            'sale' => $sale,
        ]);
    }

    private function createSaleFromCart(
        Request $request
    ): Sale {
        $cart = $request->session()->get(
            'cart',
            []
        );

        if (empty($cart)) {
            throw new RuntimeException(
                'Tu carrito está vacío.'
            );
        }

        $productIds = collect($cart)
            ->filter(function ($quantity) {
                return (int) $quantity > 0;
            })
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($productIds)) {
            throw new RuntimeException(
                'Tu carrito no contiene productos válidos.'
            );
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0;

        foreach ($cart as $productId => $cartQuantity) {
            $productId = (int) $productId;
            $quantity = (int) $cartQuantity;

            if ($quantity < 1) {
                continue;
            }

            $product = $products->get($productId);

            if (! $product) {
                throw new RuntimeException(
                    'Uno de los productos de tu carrito ya no está disponible.'
                );
            }

            if ($product->stock < $quantity) {
                throw new RuntimeException(
                    "No hay suficiente stock para " .
                    "\"{$product->name}\". " .
                    "Stock disponible: {$product->stock}."
                );
            }

            $unitPrice = (float) $product->price;

            $itemSubtotal = round(
                $unitPrice * $quantity,
                2
            );

            $subtotal += $itemSubtotal;

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
            ];
        }

        if (empty($items)) {
            throw new RuntimeException(
                'No hay productos válidos para generar la venta.'
            );
        }

        $subtotal = round(
            $subtotal,
            2
        );

        $discount = 0;

        $total = round(
            max(
                $subtotal - $discount,
                0
            ),
            2
        );

        $sale = Sale::create([
            'folio' => $this->generateFolio(),
            'mercadopago_order_id' => null,
            'customer_id' => $request
                ->user()
                ?->id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'sales_channel' => 'web',
            'status' => 'pending',
            'notes' => null,
            'sold_at' => null,
        ]);

        foreach ($items as $item) {
            $sale->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        /**
         * El pago todavía no está confirmado.
         *
         * Por eso:
         * - No se crea SalePayment.
         * - No se descuenta stock.
         *
         * Eso ocurrirá cuando Mercado Pago confirme
         * correctamente el pago mediante Webhook.
         */
        return $sale;
    }

    private function generateFolio(): string
    {
        do {
            $folio =
                'V-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(
                    Str::random(6)
                );
        } while (
            Sale::query()
                ->where('folio', $folio)
                ->exists()
        );

        return $folio;
    }
}