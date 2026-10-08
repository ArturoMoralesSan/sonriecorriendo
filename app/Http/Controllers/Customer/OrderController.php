<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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
     * Mostrar detalle de un pedido.
     */
    public function show(
        Request $request,
        Sale $sale
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | SEGURIDAD
        |--------------------------------------------------------------------------
        | El cliente solamente puede consultar sus propios pedidos.
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $sale->customer_id === (int) $request->user()->id,
            404
        );

        $sale->load([
            'items.product:id,name,image,price',
            'payments.paymentMethod:id,name',
        ]);

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
                'items' => $items,
                'payments' => $payments,
            ],
        ]);
    }
}