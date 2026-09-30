<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\View\Composers\MenuComposer;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $cart = $request->session()->get('cart', []);

        $cartItems = 0;
        $cartProducts = [];

        /*
        |--------------------------------------------------------------------------
        | Productos del carrito
        |--------------------------------------------------------------------------
        */

        if (! empty($cart)) {
            $productIds = array_map(
                'intval',
                array_keys($cart)
            );

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            foreach ($cart as $productId => $quantity) {
                $productId = (int) $productId;
                $quantity = (int) $quantity;

                if ($quantity < 1) {
                    continue;
                }

                $product = $products->get($productId);

                if (! $product) {
                    continue;
                }

                if ($product->stock <= 0) {
                    continue;
                }

                $quantity = min(
                    $quantity,
                    (int) $product->stock
                );

                if ($quantity < 1) {
                    continue;
                }

                $price = (float) $product->price;

                $itemSubtotal = $price * $quantity;

                $cartProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $product->image,
                    'price' => $price,
                    'quantity' => $quantity,
                    'stock' => (int) $product->stock,
                    'subtotal' => $itemSubtotal,
                ];

                $cartItems += $quantity;
            }
        }

        $cartSubtotal = collect($cartProducts)->sum(
            'subtotal'
        );

        return [
            ...parent::share($request),

            'name' => config('app.name'),

            'auth' => [
                'user' => $request->user(),
            ],

            'profile' => fn () => $request->user()?->profile,

            'sidebarOpen' => ! $request->hasCookie('sidebar_state')
                || $request->cookie('sidebar_state') === 'true',

            'menus' => fn () => app(MenuComposer::class)->getMenus(),

            'cart' => [
                'totalItems' => $cartItems,
                'subtotal' => $cartSubtotal,
                'items' => $cartProducts,
            ],
        ];
    }
}
