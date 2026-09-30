<?php

namespace App\Http\Controllers;

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

        $productIds = array_keys($cart);

        if (empty($productIds)) {
            return Inertia::render('Cart', [
                'items' => [],
                'subtotal' => 0,
                'totalItems' => 0,
            ]);
        }

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
            $itemSubtotal = $price * $quantity;

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
            'subtotal' => $subtotal,
            'totalItems' => $totalItems,
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

        $newQuantity = $currentQuantity + $requestedQuantity;

        if ($newQuantity > $product->stock) {
            $newQuantity = $product->stock;
        }

        $cart[$productId] = $newQuantity;

        $request->session()->put('cart', $cart);

        return back()->with(
            'success',
            'Producto agregado al carrito.'
        );
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

        $quantity = min(
            (int) $validated['quantity'],
            $product->stock
        );

        $cart[$productId] = $quantity;

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

        return back()->with(
            'success',
            'Producto eliminado del carrito.'
        );
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back()->with(
            'success',
            'Carrito vaciado.'
        );
    }
}
