<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request): Response
    {
        $cart = $request->session()->get('cart', []);

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'street',
                'exterior_number',
                'interior_number',
                'neighborhood',
                'postal_code',
                'city',
                'state',
                'references',
                'phone',
                'opening_time',
                'closing_time',
            ])
            ->map(fn (Branch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->full_address,
                'phone' => $branch->phone,
                'opening_time' => $branch->opening_time,
                'closing_time' => $branch->closing_time,
            ])
            ->values();

        if (empty($cart)) {
            return Inertia::render('Cart', [
                'items' => [],
                'subtotal' => 0,
                'totalItems' => 0,
                'branches' => $branches,
            ]);
        }

        $productIds = array_keys($cart);

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0;
        $totalItems = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $products->get((int) $productId);

            if (! $product) {
                continue;
            }

            $quantity = (int) $quantity;

            if ($quantity < 1) {
                continue;
            }

            $quantity = min($quantity, $product->stock);

            if ($quantity < 1) {
                continue;
            }

            $price = (float) $product->price;
            $itemSubtotal = round($price * $quantity, 2);

            $items[] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->image,
                'price' => $price,
                'quantity' => $quantity,
                'stock' => $product->stock,
                'subtotal' => $itemSubtotal,
            ];

            $subtotal += $itemSubtotal;
            $totalItems += $quantity;
        }

        return Inertia::render('Cart', [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'totalItems' => $totalItems,
            'branches' => $branches,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()
            ->where('id', $validated['product_id'])
            ->where('is_active', true)
            ->firstOrFail();

        if ($product->stock <= 0) {
            return back()->with('error', 'Este producto está agotado.');
        }

        $cart = $request->session()->get('cart', []);

        $productId = (string) $product->id;
        $currentQuantity = (int) ($cart[$productId] ?? 0);
        $requestedQuantity = (int) $validated['quantity'];

        $cart[$productId] = min(
            $currentQuantity + $requestedQuantity,
            $product->stock
        );

        $request->session()->put('cart', $cart);

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()
            ->where('id', $validated['product_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $cart = $request->session()->get('cart', []);
        $productId = (string) $product->id;

        if (! array_key_exists($productId, $cart)) {
            return back();
        }

        if ($product->stock <= 0) {
            unset($cart[$productId]);

            $request->session()->put('cart', $cart);

            return back()->with(
                'error',
                'El producto ya no está disponible.'
            );
        }

        $cart[$productId] = min(
            (int) $validated['quantity'],
            $product->stock
        );

        $request->session()->put('cart', $cart);

        return back();
    }

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $cart = $request->session()->get('cart', []);

        unset($cart[(string) $validated['product_id']]);

        $request->session()->put('cart', $cart);

        return back()->with('success', 'Producto eliminado del carrito.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back()->with('success', 'Carrito vaciado.');
    }
}