<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        /*
        |--------------------------------------------------------------------------
        | DASHBOARD CUSTOMER
        |--------------------------------------------------------------------------
        */

        if ($request->user()?->hasRole('customer')) {
            $customer = $request->user();

            /*
            |--------------------------------------------------------------------------
            | FECHAS SELECCIONADAS
            |--------------------------------------------------------------------------
            */

            $now = Carbon::now();

            try {
                $startDate = $request->filled('start_date')
                    ? Carbon::createFromFormat(
                        'Y-m-d',
                        $request->input('start_date')
                    )->startOfDay()
                    : $now->copy()->startOfMonth();

                $endDate = $request->filled('end_date')
                    ? Carbon::createFromFormat(
                        'Y-m-d',
                        $request->input('end_date')
                    )->endOfDay()
                    : $now->copy()->endOfMonth();
            } catch (\Throwable) {
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
            }

            /*
            |--------------------------------------------------------------------------
            | CORREGIR RANGO INVERTIDO
            |--------------------------------------------------------------------------
            */

            if ($startDate->gt($endDate)) {
                [$startDate, $endDate] = [
                    $endDate->copy()->startOfDay(),
                    $startDate->copy()->endOfDay(),
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | PERIODO ANTERIOR EQUIVALENTE
            |--------------------------------------------------------------------------
            */

            $periodDays = $startDate->diffInDays($endDate) + 1;

            $previousEndDate = $startDate
                ->copy()
                ->subDay()
                ->endOfDay();

            $previousStartDate = $previousEndDate
                ->copy()
                ->subDays($periodDays - 1)
                ->startOfDay();

            /*
            |--------------------------------------------------------------------------
            | PEDIDOS DEL CLIENTE
            |--------------------------------------------------------------------------
            */

            $currentOrdersQuery = Sale::query()
                ->where('customer_id', $customer->id)
                ->whereBetween('sold_at', [
                    $startDate,
                    $endDate,
                ]);

            $previousOrdersQuery = Sale::query()
                ->where('customer_id', $customer->id)
                ->whereBetween('sold_at', [
                    $previousStartDate,
                    $previousEndDate,
                ]);

            /*
            |--------------------------------------------------------------------------
            | TOTAL DE PEDIDOS
            |--------------------------------------------------------------------------
            */

            $currentOrders = (clone $currentOrdersQuery)->count();

            $previousOrders = (clone $previousOrdersQuery)->count();

            /*
            |--------------------------------------------------------------------------
            | PEDIDOS COMPLETADOS
            |--------------------------------------------------------------------------
            */

            $completedOrders = (clone $currentOrdersQuery)
                ->where('status', 'completed')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | PEDIDOS PENDIENTES
            |--------------------------------------------------------------------------
            */

            $pendingOrders = (clone $currentOrdersQuery)
                ->whereIn('status', [
                    'pending',
                    'pending_payment',
                ])
                ->count();

            /*
            |--------------------------------------------------------------------------
            | PRODUCTOS COMPRADOS
            |--------------------------------------------------------------------------
            */

            $currentProductsSold = (int) SaleItem::query()
                ->whereHas('sale', function ($query) use (
                    $customer,
                    $startDate,
                    $endDate
                ) {
                    $query
                        ->where('customer_id', $customer->id)
                        ->where('status', 'completed')
                        ->whereBetween('sold_at', [
                            $startDate,
                            $endDate,
                        ]);
                })
                ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | GASTO TOTAL DEL CLIENTE
            |--------------------------------------------------------------------------
            |
            | Se utiliza sales.total.
            |
            | No se suman sale_payments.amount porque un pedido
            | puede tener más de un registro de pago.
            |
            */

            $currentRevenue = (float) (
                (clone $currentOrdersQuery)
                    ->where('status', 'completed')
                    ->sum('total')
            );

            /*
            |--------------------------------------------------------------------------
            | DATOS DEL PERIODO ANTERIOR
            |--------------------------------------------------------------------------
            */

            $previousProductsSold = (int) SaleItem::query()
                ->whereHas('sale', function ($query) use (
                    $customer,
                    $previousStartDate,
                    $previousEndDate
                ) {
                    $query
                        ->where('customer_id', $customer->id)
                        ->where('status', 'completed')
                        ->whereBetween('sold_at', [
                            $previousStartDate,
                            $previousEndDate,
                        ]);
                })
                ->sum('quantity');

            $previousRevenue = (float) (
                (clone $previousOrdersQuery)
                    ->where('status', 'completed')
                    ->sum('total')
            );

            /*
            |--------------------------------------------------------------------------
            | CRECIMIENTOS
            |--------------------------------------------------------------------------
            */

            $orderGrowth = $this->calculateGrowth(
                $previousOrders,
                $currentOrders
            );

            $productGrowth = $this->calculateGrowth(
                $previousProductsSold,
                $currentProductsSold
            );

            $revenueGrowth = $this->calculateGrowth(
                $previousRevenue,
                $currentRevenue
            );

            /*
            |--------------------------------------------------------------------------
            | PEDIDOS RECIENTES
            |--------------------------------------------------------------------------
            */

            $recentSales = Sale::query()
                ->with([
                    'items:id,sale_id,product_id,quantity',
                    'items.product:id,name',
                ])
                ->where('customer_id', $customer->id)
                ->whereBetween('sold_at', [
                    $startDate,
                    $endDate,
                ])
                ->orderByDesc('sold_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            $recentOrders = $recentSales
                ->map(function (Sale $sale) {
                    return [
                        'id' => $sale->id,
                        'folio' => $sale->folio,
                        'total' => (float) $sale->total,
                        'status' => $sale->status,
                        'sold_at' => $sale->sold_at
                            ? $sale->sold_at->toISOString()
                            : null,
                        'items_count' => (int) $sale->items->sum(
                            'quantity'
                        ),
                    ];
                })
                ->values();

            /*
            |--------------------------------------------------------------------------
            | DASHBOARD CUSTOMER
            |--------------------------------------------------------------------------
            */

            return Inertia::render('customer/Dashboard', [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                ],

                'stats' => [
                    'total_orders' => $currentOrders,
                    'completed_orders' => $completedOrders,
                    'pending_orders' => $pendingOrders,
                    'total_spent' => $currentRevenue,
                ],

                'recentOrders' => $recentOrders,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD ADMINISTRATIVO
        |--------------------------------------------------------------------------
        */

        $now = Carbon::now();

        try {
            $startDate = $request->filled('start_date')
                ? Carbon::createFromFormat(
                    'Y-m-d',
                    $request->input('start_date')
                )->startOfDay()
                : $now->copy()->startOfMonth();

            $endDate = $request->filled('end_date')
                ? Carbon::createFromFormat(
                    'Y-m-d',
                    $request->input('end_date')
                )->endOfDay()
                : $now->copy()->endOfMonth();
        } catch (\Throwable) {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        }

        /*
        |--------------------------------------------------------------------------
        | CORREGIR RANGO INVERTIDO
        |--------------------------------------------------------------------------
        */

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [
                $endDate->copy()->startOfDay(),
                $startDate->copy()->endOfDay(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | PERIODO ANTERIOR EQUIVALENTE
        |--------------------------------------------------------------------------
        */

        $periodDays = $startDate->diffInDays($endDate) + 1;

        $previousEndDate = $startDate
            ->copy()
            ->subDay()
            ->endOfDay();

        $previousStartDate = $previousEndDate
            ->copy()
            ->subDays($periodDays - 1)
            ->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | USUARIOS
        |--------------------------------------------------------------------------
        */

        $totalUsers = User::count();

        $currentUsers = User::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->count();

        $previousUsers = User::query()
            ->whereBetween('created_at', [
                $previousStartDate,
                $previousEndDate,
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | VENTAS
        |--------------------------------------------------------------------------
        */

        $currentSalesQuery = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sold_at', [
                $startDate,
                $endDate,
            ]);

        $previousSalesQuery = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sold_at', [
                $previousStartDate,
                $previousEndDate,
            ]);

        $currentOrders = (clone $currentSalesQuery)->count();

        $previousOrders = (clone $previousSalesQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | INGRESOS
        |--------------------------------------------------------------------------
        */

        $currentRevenue = (float) (
            (clone $currentSalesQuery)->sum('total')
        );

        $previousRevenue = (float) (
            (clone $previousSalesQuery)->sum('total')
        );

        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS VENDIDOS
        |--------------------------------------------------------------------------
        */

        $currentProductsSold = (int) SaleItem::query()
            ->whereHas('sale', function ($query) use (
                $startDate,
                $endDate
            ) {
                $query
                    ->where('status', 'completed')
                    ->whereBetween('sold_at', [
                        $startDate,
                        $endDate,
                    ]);
            })
            ->sum('quantity');

        $previousProductsSold = (int) SaleItem::query()
            ->whereHas('sale', function ($query) use (
                $previousStartDate,
                $previousEndDate
            ) {
                $query
                    ->where('status', 'completed')
                    ->whereBetween('sold_at', [
                        $previousStartDate,
                        $previousEndDate,
                    ]);
            })
            ->sum('quantity');

        /*
        |--------------------------------------------------------------------------
        | CRECIMIENTOS
        |--------------------------------------------------------------------------
        */

        $userGrowth = $this->calculateGrowth(
            $previousUsers,
            $currentUsers
        );

        $orderGrowth = $this->calculateGrowth(
            $previousOrders,
            $currentOrders
        );

        $productGrowth = $this->calculateGrowth(
            $previousProductsSold,
            $currentProductsSold
        );

        $revenueGrowth = $this->calculateGrowth(
            $previousRevenue,
            $currentRevenue
        );

        /*
        |--------------------------------------------------------------------------
        | VENTAS POR DÍA
        |--------------------------------------------------------------------------
        */

        $dailyRevenue = Sale::query()
            ->selectRaw('DATE(sold_at) as sale_date')
            ->selectRaw('SUM(total) as revenue')
            ->selectRaw('COUNT(*) as orders')
            ->where('status', 'completed')
            ->whereBetween('sold_at', [
                $startDate,
                $endDate,
            ])
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $revenueChart = [];

        $date = $startDate->copy()->startOfDay();

        while ($date->lte($endDate)) {
            $dateKey = $date->format('Y-m-d');

            $dayData = $dailyRevenue->get($dateKey);

            $revenueChart[] = [
                'date' => $date->format('d/m'),
                'revenue' => $dayData
                    ? (float) $dayData->revenue
                    : 0,
                'orders' => $dayData
                    ? (int) $dayData->orders
                    : 0,
            ];

            $date->addDay();
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS MÁS VENDIDOS
        |--------------------------------------------------------------------------
        */

        $topProducts = SaleItem::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as quantity')
            ->selectRaw('SUM(subtotal) as revenue')
            ->whereHas('sale', function ($query) use (
                $startDate,
                $endDate
            ) {
                $query
                    ->where('status', 'completed')
                    ->whereBetween('sold_at', [
                        $startDate,
                        $endDate,
                    ]);
            })
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $productChart = $topProducts
            ->map(function ($item) {
                return [
                    'name' => $item->product?->name
                        ?? 'Producto eliminado',
                    'quantity' => (int) $item->quantity,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | MÉTODOS DE PAGO
        |--------------------------------------------------------------------------
        */

        $paymentMethods = DB::table('sale_payments')
            ->join(
                'sales',
                'sales.id',
                '=',
                'sale_payments.sale_id'
            )
            ->join(
                'payment_methods',
                'payment_methods.id',
                '=',
                'sale_payments.payment_method_id'
            )
            ->select(
                'payment_methods.id',
                'payment_methods.name'
            )
            ->selectRaw(
                'SUM(sale_payments.amount) as amount'
            )
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [
                $startDate,
                $endDate,
            ])
            ->groupBy(
                'payment_methods.id',
                'payment_methods.name'
            )
            ->orderByDesc('amount')
            ->get();

        $paymentMethodChart = $paymentMethods
            ->map(function ($payment) {
                return [
                    'name' => $payment->name,
                    'amount' => (float) $payment->amount,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | NUEVOS USUARIOS POR DÍA
        |--------------------------------------------------------------------------
        */

        $newUsersByDay = User::query()
            ->selectRaw(
                'DATE(created_at) as registration_date'
            )
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->groupBy(
                DB::raw('DATE(created_at)')
            )
            ->orderBy('registration_date')
            ->get()
            ->keyBy('registration_date');

        $registrationsChart = [];

        $registrationDate = $startDate
            ->copy()
            ->startOfDay();

        while ($registrationDate->lte($endDate)) {
            $dateKey = $registrationDate->format('Y-m-d');

            $dayData = $newUsersByDay->get($dateKey);

            $registrationsChart[] = [
                'date' => $registrationDate->format('d/m'),
                'registrations' => $dayData
                    ? (int) $dayData->total
                    : 0,
            ];

            $registrationDate->addDay();
        }

        $registrationsTotal = collect($registrationsChart)
            ->sum('registrations');

        /*
        |--------------------------------------------------------------------------
        | VENTAS RECIENTES
        |--------------------------------------------------------------------------
        */

        $recentSales = Sale::query()
            ->with([
                'customer:id,name,email',
                'items.product:id,name',
            ])
            ->where('status', 'completed')
            ->whereBetween('sold_at', [
                $startDate,
                $endDate,
            ])
            ->orderByDesc('sold_at')
            ->limit(5)
            ->get();

        $recentOrders = $recentSales
            ->map(function (Sale $sale) {
                $products = $sale->items
                    ->map(function ($item) {
                        $name = $item->product?->name
                            ?? 'Producto eliminado';

                        return $item->quantity > 1
                            ? "{$name} x{$item->quantity}"
                            : $name;
                    })
                    ->implode(', ');

                return [
                    'id' => $sale->id,
                    'folio' => $sale->folio,
                    'customer' => $sale->customer?->name
                        ?? 'Cliente general',
                    'email' => $sale->customer?->email
                        ?? '',
                    'product' => $products
                        ?: 'Sin productos',
                    'amount' => (float) $sale->total,
                    'status' => $this->formatSaleStatus(
                        $sale->status
                    ),
                    'date' => $sale->sold_at
                        ? $sale->sold_at
                            ->locale('es')
                            ->translatedFormat(
                                'd M Y, H:i'
                            )
                        : $sale->created_at
                            ->locale('es')
                            ->translatedFormat(
                                'd M Y, H:i'
                            ),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | ETIQUETA DEL PERIODO
        |--------------------------------------------------------------------------
        */

        $periodLabel = $startDate->isSameDay($endDate)
            ? $startDate
                ->locale('es')
                ->translatedFormat(
                    'd \d\e F Y'
                )
            : $startDate
                ->locale('es')
                ->translatedFormat(
                    'd \d\e F Y'
                )
                . ' - '
                . $endDate
                    ->locale('es')
                    ->translatedFormat(
                        'd \d\e F Y'
                    );

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA ADMIN
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Dashboard', [
            'stats' => [
                'users' => $totalUsers,
                'orders' => $currentOrders,
                'productsSold' => $currentProductsSold,
                'revenue' => $currentRevenue,
                'growth' => [
                    'users' => $userGrowth,
                    'orders' => $orderGrowth,
                    'productsSold' => $productGrowth,
                    'revenue' => $revenueGrowth,
                ],
            ],

            'revenueChart' => $revenueChart,

            'productChart' => $productChart,

            'paymentMethodChart' => $paymentMethodChart,

            'registrationsChart' => $registrationsChart,

            'registrationsTotal' => $registrationsTotal,

            'recentOrders' => $recentOrders,

            'period' => [
                'label' => $periodLabel,
                'month' => $periodLabel,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    private function calculateGrowth(
        float|int $previous,
        float|int $current
    ): float {
        if ((float) $previous === 0.0) {
            return $current > 0
                ? 100
                : 0;
        }

        return round(
            (($current - $previous) / $previous) * 100,
            1
        );
    }

    private function formatSaleStatus(
        string $status
    ): string {
        return match ($status) {
            'completed' => 'Completado',
            'pending' => 'Pendiente',
            'pending_payment' => 'Pendiente de pago',
            'paid' => 'Pagado',
            'cancelled' => 'Cancelado',
            'canceled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            'failed' => 'Fallido',
            default => ucfirst($status),
        };
    }
}