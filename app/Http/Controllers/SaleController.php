<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DeliveryAddress;
use App\Models\Product;
use App\Models\Sale;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $validated = Validator::make($request->all(), [
            'delivery_method' => [
                'required',
                Rule::in([
                    DeliveryAddress::METHOD_HOME,
                    DeliveryAddress::METHOD_BRANCH,
                ]),
            ],

            'branch_id' => [
                'required_if:delivery_method,' . DeliveryAddress::METHOD_BRANCH,
                'nullable',
                'integer',
                Rule::exists('branches', 'id')
                    ->where('is_active', true),
            ],

            'street' => [
                'required_if:delivery_method,' . DeliveryAddress::METHOD_HOME,
                'nullable',
                'string',
                'max:255',
            ],

            'exterior_number' => [
                'required_if:delivery_method,' . DeliveryAddress::METHOD_HOME,
                'nullable',
                'string',
                'max:50',
            ],

            'interior_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'neighborhood' => [
                'required_if:delivery_method,' . DeliveryAddress::METHOD_HOME,
                'nullable',
                'string',
                'max:255',
            ],

            'postal_code' => [
                'required_if:delivery_method,' . DeliveryAddress::METHOD_HOME,
                'nullable',
                'string',
                'max:10',
            ],

            'city' => [
                'required_if:delivery_method,' . DeliveryAddress::METHOD_HOME,
                'nullable',
                'string',
                'max:255',
            ],

            'state' => [
                'required_if:delivery_method,' . DeliveryAddress::METHOD_HOME,
                'nullable',
                'string',
                'max:255',
            ],

            'references' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'delivery_method.required' => 'Selecciona una modalidad de entrega.',
            'delivery_method.in' => 'La modalidad de entrega seleccionada no es válida.',
            'branch_id.required_if' => 'Selecciona la sucursal donde recogerás tu pedido.',
            'branch_id.exists' => 'La sucursal seleccionada no está disponible.',
            'street.required_if' => 'Escribe la calle de entrega.',
            'exterior_number.required_if' => 'Escribe el número exterior.',
            'neighborhood.required_if' => 'Escribe la colonia.',
            'postal_code.required_if' => 'Escribe el código postal.',
            'city.required_if' => 'Escribe la ciudad.',
            'state.required_if' => 'Escribe el estado.',
        ])->validate();

        try {
            $sale = DB::transaction(function () use ($request, $validated) {
                $sale = $this->createSaleFromCart($request);

                if (
                    $validated['delivery_method']
                    === DeliveryAddress::METHOD_BRANCH
                ) {
                    $branch = Branch::query()
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->findOrFail($validated['branch_id']);

                    $sale->deliveryAddress()->create([
                        'delivery_method' => DeliveryAddress::METHOD_BRANCH,
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'branch_address' => $branch->full_address,
                    ]);
                } else {
                    $sale->deliveryAddress()->create([
                        'delivery_method' => DeliveryAddress::METHOD_HOME,
                        'street' => $validated['street'],
                        'exterior_number' => $validated['exterior_number'],
                        'interior_number' => $validated['interior_number'] ?? null,
                        'neighborhood' => $validated['neighborhood'],
                        'postal_code' => $validated['postal_code'],
                        'city' => $validated['city'],
                        'state' => $validated['state'],
                        'references' => $validated['references'] ?? null,
                    ]);
                }

                return $sale;
            });

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

            $returnUrl = route('sales.show', $sale);

            $order = $mercadoPago->createOrder(
                externalReference: $sale->folio,
                description: 'Pedido Sonríe Corriendo ' . $sale->folio,
                total: (float) $sale->total,
                items: $items,
                payerEmail: $request->user()?->email,
                successUrl: $returnUrl,
                failureUrl: $returnUrl,
                pendingUrl: $returnUrl,
            );

            if (empty($order->id)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió el ID de la Order.'
                );
            }

            if (empty($order->checkout_url)) {
                throw new RuntimeException(
                    'Mercado Pago no devolvió la URL de Checkout.'
                );
            }

            $sale->update([
                'mercadopago_order_id' => $order->id,
            ]);

            $request->session()->forget('cart');

            return response()->json([
                'checkout_url' => $order->checkout_url,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible iniciar el checkout.',
                'errors' => [
                    'sale' => [
                        $exception instanceof RuntimeException
                            ? $exception->getMessage()
                            : 'Ocurrió un error al preparar tu pedido. Intenta nuevamente.',
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
            'deliveryAddress.branch',
        ]);

        return Inertia::render('SaleShow', [
            'sale' => $sale,
        ]);
    }

    private function createSaleFromCart(Request $request): Sale
    {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            throw new RuntimeException('Tu carrito está vacío.');
        }

        $productIds = collect($cart)
            ->filter(fn ($quantity) => (int) $quantity > 0)
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
                    "No hay suficiente stock para \"{$product->name}\". " .
                    "Stock disponible: {$product->stock}."
                );
            }

            $unitPrice = (float) $product->price;
            $itemSubtotal = round($unitPrice * $quantity, 2);

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

        $subtotal = round($subtotal, 2);
        $discount = 0;
        $total = round(max($subtotal - $discount, 0), 2);

        $sale = Sale::create([
            'folio' => $this->generateFolio(),
            'mercadopago_order_id' => null,
            'customer_id' => $request->user()?->id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'sales_channel' => 'web',
            'status' => 'pending',
            'notes' => null,
            'sold_at' => null,
        ]);

        foreach ($items as $item) {
            $sale->items()->create($item);
        }

        return $sale;
    }

    private function generateFolio(): string
    {
        do {
            $folio = 'V-' . now()->format('Ymd') . '-' .
                strtoupper(Str::random(6));
        } while (Sale::query()->where('folio', $folio)->exists());

        return $folio;
    }
}