<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'type' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            $directory = storage_path('app/public/products');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $image = $request->file('image');

            $filename =
                uniqid() .
                '.' .
                $image->getClientOriginalExtension();

            $image->move($directory, $filename);

            $validated['image'] = "products/{$filename}";
        }

        Product::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Producto creado correctamente.',
        ]);

        return redirect()
            ->route('admin.products.index');
    }

    public function show(Product $product): Response
    {
        return Inertia::render('admin/products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('admin/products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,slug,' . $product->id,
            ],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'type' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->boolean('remove_image') && $product->image) {
            $path = storage_path('app/public/' . $product->image);

            if (is_file($path)) {
                unlink($path);
            }

            $validated['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                $oldPath = storage_path('app/public/' . $product->image);

                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $directory = storage_path('app/public/products');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $image = $request->file('image');

            $filename =
                uniqid() .
                '.' .
                $image->getClientOriginalExtension();

            $image->move($directory, $filename);

            $validated['image'] = "products/{$filename}";
        } else {
            unset($validated['image']);
        }

        unset($validated['remove_image']);

        $product->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Producto actualizado correctamente.',
        ]);

        return redirect()
            ->route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            $imagePath = storage_path('app/public/' . $product->image);

            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        $product->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Producto eliminado correctamente.',
        ]);

        return redirect()
            ->route('admin.products.index');
    }
}