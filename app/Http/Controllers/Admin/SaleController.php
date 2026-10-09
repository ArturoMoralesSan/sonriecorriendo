<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DeliveryAddress;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SaleController extends Controller
{
    /**
     * Listado de ventas.
     */
    public function index(Request $request): Response
    {
        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        $sales = Sale::query()
            ->with([
                'customer',
                'items.product',
                'payments.paymentMethod',
                'deliveryAddress.branch',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('folio', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('sales_channel', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('sold_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/sales/Index', [
            'sales' => $sales,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Formulario para crear una venta.
     */
    public function create(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'image',
                'type',
                'year',
                'price',
                'stock',
                'is_active',
            ]);

        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        $branches = $this->activeBranches();

        return Inertia::render('admin/sales/Create', [
            'products' => $products,
            'paymentMethods' => $paymentMethods,
            'branches' => $branches,
        ]);
    }

    /**
     * Busca un cliente mediante su código QR.
     */
    public function customerByQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => [
                'required',
                'string',
            ],
        ]);

        $user = User::query()
            ->where('qr_token', $validated['qr_token'])
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'No se encontró ningún cliente con ese código QR.',
            ], 404);
        }

        return response()->json([
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'qr_token' => $user->qr_token,
            ],
        ]);
    }

    /**
     * Guarda una venta.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'subtotal' => [
                'required',
                'numeric',
                'min:0',
            ],

            'discount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'total' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sales_channel' => [
                'required',
                'string',
                'in:counter,branch,web,app',
            ],

            'status' => [
                'required',
                'string',
                'in:pending,paid,partially_paid,cancelled,refunded',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'sold_at' => [
                'nullable',
                'date',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'payments' => [
                'required',
                'array',
                'min:1',
            ],

            'payments.*.payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id',
            ],

            'payments.*.amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'payments.*.reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'payments.*.notes' => [
                'nullable',
                'string',
            ],
        ]);

        try {
            $sale = DB::transaction(function () use ($validated) {
                /*
                 * Productos y stock.
                 */
                $productIds = collect($validated['items'])
                    ->pluck('product_id')
                    ->values()
                    ->all();

                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                /*
                 * Calcular productos y subtotal real.
                 */
                $items = [];
                $calculatedSubtotal = 0;

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);

                    if (! $product) {
                        throw new \RuntimeException(
                            'Uno de los productos seleccionados ya no existe.'
                        );
                    }

                    $quantity = (int) $item['quantity'];

                    if ($product->stock < $quantity) {
                        throw new \RuntimeException(
                            "No hay suficiente stock para el producto \"{$product->name}\". Stock disponible: {$product->stock}."
                        );
                    }

                    $unitPrice = (float) $product->price;
                    $itemSubtotal = $unitPrice * $quantity;

                    $calculatedSubtotal += $itemSubtotal;

                    $items[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                /*
                 * Descuento.
                 */
                $discount = min(
                    max((float) $validated['discount'], 0),
                    $calculatedSubtotal
                );

                /*
                 * Total real.
                 */
                $calculatedTotal = max(
                    $calculatedSubtotal - $discount,
                    0
                );

                /*
                 * Validar los totales enviados.
                 */
                if (
                    abs(
                        (float) $validated['subtotal'] -
                        $calculatedSubtotal
                    ) > 0.01
                ) {
                    throw new \RuntimeException(
                        'El subtotal de la venta no coincide con los productos seleccionados.'
                    );
                }

                if (
                    abs(
                        (float) $validated['total'] -
                        $calculatedTotal
                    ) > 0.01
                ) {
                    throw new \RuntimeException(
                        'El total de la venta no coincide con los productos seleccionados.'
                    );
                }

                /*
                 * Validar métodos de pago.
                 */
                $paymentMethodIds = collect($validated['payments'])
                    ->pluck('payment_method_id')
                    ->unique()
                    ->values()
                    ->all();

                $paymentMethods = PaymentMethod::query()
                    ->whereIn('id', $paymentMethodIds)
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('id');

                if ($paymentMethods->count() !== count($paymentMethodIds)) {
                    throw new \RuntimeException(
                        'Uno de los métodos de pago seleccionados no está disponible.'
                    );
                }

                $paymentsTotal = collect($validated['payments'])
                    ->sum(fn ($payment) => (float) $payment['amount']);

                if ($paymentsTotal + 0.01 < $calculatedTotal) {
                    throw new \RuntimeException(
                        'El importe de los pagos no cubre el total de la venta.'
                    );
                }

                /*
                 * Validar pagos que no sean en efectivo.
                 */
                $cashTotal = 0;
                $nonCashTotal = 0;

                foreach ($validated['payments'] as $payment) {
                    $paymentMethod = $paymentMethods->get(
                        $payment['payment_method_id']
                    );

                    if ($paymentMethod && $paymentMethod->code === 'cash') {
                        $cashTotal += (float) $payment['amount'];
                    } else {
                        $nonCashTotal += (float) $payment['amount'];
                    }
                }

                if ($nonCashTotal > $calculatedTotal + 0.01) {
                    throw new \RuntimeException(
                        'Los pagos que no son en efectivo no pueden superar el total de la venta.'
                    );
                }

                /*
                 * Estado real de la venta.
                 */
                $status = $validated['status'];

                if (
                    $status === 'paid' &&
                    $paymentsTotal + 0.01 < $calculatedTotal
                ) {
                    throw new \RuntimeException(
                        'Una venta marcada como pagada debe estar cubierta por completo.'
                    );
                }

                if (
                    $status === 'pending' &&
                    $paymentsTotal + 0.01 >= $calculatedTotal
                ) {
                    $status = 'paid';
                }

                /*
                 * Folio.
                 */
                $folio = $this->generateFolio();

                /*
                 * Crear venta.
                 */
                $sale = Sale::create([
                    'folio' => $folio,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'subtotal' => $calculatedSubtotal,
                    'discount' => $discount,
                    'total' => $calculatedTotal,
                    'sales_channel' => $validated['sales_channel'],
                    'status' => $status,
                    'notes' => $validated['notes'] ?? null,
                    'sold_at' => $validated['sold_at'] ?? now(),
                ]);

                /*
                 * Crear partidas y descontar stock.
                 */
                foreach ($items as $item) {
                    $sale->items()->create([
                        'product_id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    $item['product']->decrement(
                        'stock',
                        $item['quantity']
                    );
                }

                /*
                 * Crear pagos.
                 */
                foreach ($validated['payments'] as $payment) {
                    $sale->payments()->create([
                        'payment_method_id' => $payment['payment_method_id'],
                        'amount' => $payment['amount'],
                        'reference' => $payment['reference'] ?? null,
                        'notes' => $payment['notes'] ?? null,
                    ]);
                }

                return $sale;
            });

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => "Venta {$sale->folio} creada correctamente.",
            ]);

            return redirect()->route('admin.sales.index');
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'sale' => $exception->getMessage(),
                ]);
        }
    }

    /**
     * Mostrar una venta con su dirección y sucursal.
     */
    public function show(Sale $sale): Response
    {
        $sale->load([
            'customer',
            'items.product',
            'payments.paymentMethod',
            'deliveryAddress.branch',
        ]);

        return Inertia::render('admin/sales/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(Sale $sale): Response
    {
        $sale->load([
            'customer',
            'items.product',
            'payments.paymentMethod',
            'deliveryAddress.branch',
        ]);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'image',
                'type',
                'year',
                'price',
                'stock',
                'is_active',
            ]);

        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        $branches = $this->activeBranches();

        return Inertia::render('admin/sales/Edit', [
            'sale' => $sale,
            'products' => $products,
            'paymentMethods' => $paymentMethods,
            'branches' => $branches,
        ]);
    }

    /**
     * Actualiza una venta.
     */
    public function update(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:pending,paid,partially_paid,cancelled,refunded',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $sale->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Venta {$sale->folio} actualizada correctamente.",
        ]);

        return redirect()->route('admin.sales.show', $sale);
    }

    /**
     * Cancelar una venta y devolver el stock.
     */
    public function destroy(Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($sale) {
            $sale->load('items');

            if ($sale->status !== 'cancelled') {
                foreach ($sale->items as $item) {
                    Product::query()
                        ->where('id', $item->product_id)
                        ->lockForUpdate()
                        ->increment('stock', $item->quantity);
                }
            }

            $sale->update([
                'status' => 'cancelled',
            ]);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Venta {$sale->folio} cancelada correctamente.",
        ]);

        return redirect()->route('admin.sales.index');
    }

    /**
     * Obtener sucursales activas.
     */
    private function activeBranches()
    {
        return Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'street',
                'exterior_number',
                'interior_number',
                'neighborhood',
                'postal_code',
                'city',
                'state',
                'phone',
                'opening_time',
                'closing_time',
            ]);
    }

    /**
     * Genera un folio único para la venta.
     */
    private function generateFolio(): string
    {
        do {
            $folio = 'V-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));
        } while (
            Sale::query()
                ->where('folio', $folio)
                ->exists()
        );

        return $folio;
    }
}