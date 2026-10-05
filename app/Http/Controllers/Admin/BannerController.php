<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    /**
     * Display a listing of the banners.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $banners = Banner::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'page',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'title',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->orderBy('page')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render(
            'admin/banners/Index',
            [
                'banners' => $banners,
                'filters' => [
                    'search' => $search,
                ],
            ]
        );
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create(): Response
    {
        return Inertia::render('admin/banners/Create');
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'page' => [
                'required',
                'string',
                'in:home,galeria,productos,resultados,eventos',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,avif,gif',
                'max:5120',
            ],

            'mobile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,avif,gif',
                'max:5120',
            ],

            'button_text' => [
                'nullable',
                'string',
                'max:100',
            ],

            'button_url' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Imagen principal
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $directory = storage_path('app/public/banners');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $image = $request->file('image');

            $filename =
                uniqid().
                '.'.
                $image->getClientOriginalExtension();

            $image->move($directory, $filename);

            $validated['image'] = "banners/{$filename}";
        }

        /*
        |--------------------------------------------------------------------------
        | Imagen móvil
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('mobile_image')) {
            $directory = storage_path('app/public/banners');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $mobileImage = $request->file('mobile_image');

            $filename =
                uniqid().
                '.'.
                $mobileImage->getClientOriginalExtension();

            $mobileImage->move($directory, $filename);

            $validated['mobile_image'] = "banners/{$filename}";
        }

        Banner::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Banner creado correctamente.',
        ]);

        return redirect()
            ->route('admin.banners.index');
    }

    /**
     * Display the specified banner.
     */
    public function show(Banner $banner): Response
    {
        return Inertia::render('admin/banners/Show', [
            'banner' => $banner,
        ]);
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(Banner $banner): Response
    {
        return Inertia::render('admin/banners/Edit', [
            'banner' => $banner,
        ]);
    }

    /**
     * Update the specified banner.
     */
    public function update(
        Request $request,
        Banner $banner,
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'page' => [
                'required',
                'string',
                'in:home,galeria,productos,resultados,eventos',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,avif,gif',
                'max:5120',
            ],

            'mobile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,avif,gif',
                'max:5120',
            ],

            'remove_mobile_image' => [
                'boolean',
            ],

            'button_text' => [
                'nullable',
                'string',
                'max:100',
            ],

            'button_url' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Imagen principal
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            if ($banner->image) {
                $oldPath = storage_path(
                    'app/public/'.$banner->image
                );

                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $directory = storage_path('app/public/banners');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $image = $request->file('image');

            $filename =
                uniqid().
                '.'.
                $image->getClientOriginalExtension();

            $image->move($directory, $filename);

            $validated['image'] = "banners/{$filename}";
        } else {
            unset($validated['image']);
        }

        /*
        |--------------------------------------------------------------------------
        | Imagen móvil
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('remove_mobile_image')) {
            if ($banner->mobile_image) {
                $oldPath = storage_path(
                    'app/public/'.$banner->mobile_image
                );

                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $validated['mobile_image'] = null;
        } elseif ($request->hasFile('mobile_image')) {
            if ($banner->mobile_image) {
                $oldPath = storage_path(
                    'app/public/'.$banner->mobile_image
                );

                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $directory = storage_path('app/public/banners');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $mobileImage = $request->file('mobile_image');

            $filename =
                uniqid().
                '.'.
                $mobileImage->getClientOriginalExtension();

            $mobileImage->move($directory, $filename);

            $validated['mobile_image'] = "banners/{$filename}";
        } else {
            unset($validated['mobile_image']);
        }

        unset($validated['remove_mobile_image']);

        $banner->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Banner actualizado correctamente.',
        ]);

        return redirect()
            ->route('admin.banners.index');
    }

    /**
     * Remove the specified banner.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image) {
            $imagePath = storage_path(
                'app/public/'.$banner->image
            );

            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        if ($banner->mobile_image) {
            $mobileImagePath = storage_path(
                'app/public/'.$banner->mobile_image
            );

            if (is_file($mobileImagePath)) {
                unlink($mobileImagePath);
            }
        }

        $banner->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Banner eliminado correctamente.',
        ]);

        return redirect()
            ->route('admin.banners.index');
    }
}