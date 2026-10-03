<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceGallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RaceGalleryController extends Controller
{
    /**
     * Mostrar la galería de una carrera.
     */
    public function index(Race $race): Response
    {
        $gallery = $race->gallery()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (RaceGallery $item) {
                return [
                    'id' => $item->id,
                    'image' => $item->image,
                    'url' => Storage::disk('public')->url(
                        $item->image
                    ),
                    'sort_order' => $item->sort_order,
                    'is_active' => $item->is_active,
                    'created_at' => $item->created_at?->toISOString(),
                ];
            })
            ->values();

        return Inertia::render('admin/races/gallery/Index', [
            'race' => [
                'id' => $race->id,
                'name' => $race->name,
                'slug' => $race->slug,
            ],
            'gallery' => $gallery,
        ]);
    }

    /**
     * Subir múltiples imágenes o videos.
     */
    public function store(
        Request $request,
        Race $race,
    ): RedirectResponse {
        $validated = $request->validate([
            'images' => [
                'required',
                'array',
                'min:1',
                'max:50',
            ],

            'images.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,webm,mov',
                'max:51200',
            ],
        ], [
            'images.required' => 'Selecciona al menos una imagen o video.',
            'images.array' => 'Los archivos enviados no son válidos.',
            'images.min' => 'Selecciona al menos una imagen o video.',
            'images.max' => 'Puedes subir un máximo de 50 archivos por carga.',
            'images.*.file' => 'Todos los archivos enviados deben ser válidos.',
            'images.*.mimes' => 'Los archivos deben ser JPG, JPEG, PNG, WEBP, MP4, WEBM o MOV.',
            'images.*.max' => 'Cada archivo puede pesar como máximo 50 MB.',
        ]);

        $directory = storage_path(
            "app/public/races/gallery/{$race->id}"
        );

        if (! is_dir($directory)) {
            mkdir(
                $directory,
                0755,
                true
            );
        }

        $lastOrder = (int) $race->gallery()->max('sort_order');

        foreach ($validated['images'] as $image) {
            $lastOrder++;

            $filename =
                uniqid().
                '.'.
                $image->getClientOriginalExtension();

            $image->move(
                $directory,
                $filename
            );

            $path =
                "races/gallery/{$race->id}/{$filename}";

            RaceGallery::create([
                'race_id' => $race->id,
                'image' => $path,
                'sort_order' => $lastOrder,
                'is_active' => true,
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Las imágenes y videos se subieron correctamente.',
        ]);

        return back();
    }

    /**
     * Actualizar una imagen o video.
     */
    public function update(
        Request $request,
        Race $race,
        RaceGallery $gallery,
    ): RedirectResponse {
        if ($gallery->race_id !== $race->id) {
            abort(404);
        }

        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $gallery->update([
            'is_active' => $validated['is_active'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $validated['is_active']
                ? 'El archivo se activó correctamente.'
                : 'El archivo se desactivó correctamente.',
        ]);

        return back();
    }

    /**
     * Eliminar una imagen o video.
     */
    public function destroy(
        Race $race,
        RaceGallery $gallery,
    ): RedirectResponse {
        if ($gallery->race_id !== $race->id) {
            abort(404);
        }

        if (
            $gallery->image &&
            Storage::disk('public')->exists($gallery->image)
        ) {
            Storage::disk('public')->delete(
                $gallery->image
            );
        }

        $gallery->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'El archivo se eliminó correctamente.',
        ]);

        return back();
    }

    /**
     * Actualizar el orden de las imágenes o videos.
     */
    public function updateOrder(
        Request $request,
        Race $race,
    ): RedirectResponse {
        $validated = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.id' => [
                'required',
                'integer',
            ],

            'items.*.sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $galleryIds = $race->gallery()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        foreach ($validated['items'] as $item) {
            if (
                ! in_array(
                    (int) $item['id'],
                    $galleryIds,
                    true
                )
            ) {
                continue;
            }

            RaceGallery::where('id', $item['id'])
                ->where('race_id', $race->id)
                ->update([
                    'sort_order' => $item['sort_order'],
                ]);
        }

        return back();
    }
}