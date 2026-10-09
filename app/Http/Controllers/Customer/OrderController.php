<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAddress;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Lista de pedidos del cliente.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        $orders = Sale::query()
            ->with([
                'items.product:id,name,image',
                'payments.paymentMethod:id,name',
                'deliveryAddress.branch',
            ])
            ->where('customer_id', $request->user()->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('folio', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('items.product', function ($query) use ($search) {
                            $query->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );
                        });
                });
            })
            ->latest('sold_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('customer/orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Muestra el detalle de un pedido.
     *
     * El cliente solamente puede consultar sus propios pedidos.
     */
    public function show(
        Request $request,
        Sale $sale
    ): Response {
        abort_unless(
            (int) $sale->customer_id === (int) $request->user()->id,
            404
        );

        $sale->load([
            'items.product:id,name,image,price',
            'payments.paymentMethod:id,name',
            'deliveryAddress.branch',
        ]);

        /*
         * Productos del pedido.
         */
        $items = $sale->items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name
                        ?? 'Producto eliminado',
                    'image' => $item->product?->image,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ];
            })
            ->values();

        /*
         * Pagos del pedido.
         */
        $payments = $sale->payments
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'method' => $payment->paymentMethod?->name
                        ?? 'Método de pago',
                    'amount' => (float) $payment->amount,
                    'reference' => $payment->reference,
                    'notes' => $payment->notes,
                ];
            })
            ->values();

        /*
         * Dirección de entrega y sucursal relacionada.
         */
        $deliveryAddress = $sale->deliveryAddress;
        $branch = $deliveryAddress?->branch;

        $deliveryData = null;

        if ($deliveryAddress) {
            $deliveryData = [
                'delivery_method' => $deliveryAddress->delivery_method,
                'branch_id' => $deliveryAddress->branch_id,

                'branch_name' => $deliveryAddress->branch_name
                    ?? $branch?->name,

                'branch_address' => $deliveryAddress->branch_address
                    ?? $branch?->full_address,

                'street' => $deliveryAddress->street,
                'exterior_number' => $deliveryAddress->exterior_number,
                'interior_number' => $deliveryAddress->interior_number,
                'neighborhood' => $deliveryAddress->neighborhood,
                'postal_code' => $deliveryAddress->postal_code,
                'city' => $deliveryAddress->city,
                'state' => $deliveryAddress->state,
                'references' => $deliveryAddress->references,

                'branch' => $branch ? [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'street' => $branch->street,
                    'exterior_number' => $branch->exterior_number,
                    'interior_number' => $branch->interior_number,
                    'neighborhood' => $branch->neighborhood,
                    'postal_code' => $branch->postal_code,
                    'city' => $branch->city,
                    'state' => $branch->state,
                    'phone' => $branch->phone,
                    'full_address' => $branch->full_address,
                ] : null,
            ];
        }

        return Inertia::render('customer/orders/Show', [
            'order' => [
                'id' => $sale->id,
                'folio' => $sale->folio,
                'status' => $sale->status,
                'subtotal' => (float) $sale->subtotal,
                'discount' => (float) $sale->discount,
                'total' => (float) $sale->total,
                'sales_channel' => $sale->sales_channel,
                'notes' => $sale->notes,
                'sold_at' => $sale->sold_at?->toISOString(),
                'created_at' => $sale->created_at?->toISOString(),
                'delivery_address' => $deliveryData,
                'items' => $items,
                'payments' => $payments,
            ],
        ]);
    }
}