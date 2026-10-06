<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RouteController extends Controller
{
    /**
     * Mostrar listado de rutas.
     */
    public function index(
        Request $request
    ): Response {
        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        $routes = Route::query()
            ->with([
                'media' => function ($query) {
                    $query
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where(
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
                }
            )
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render(
            'admin/routes/Index',
            [
                'routes' => $routes,
                'filters' => [
                    'search' => $search,
                ],
            ]
        );
    }

    /**
     * Mostrar formulario para crear una nueva ruta.
     */
    public function create(): Response
    {
        return Inertia::render(
            'admin/routes/Create'
        );
    }

    /**
     * Crear una nueva ruta.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'media' => [
                'nullable',
                'array',
                'max:50',
            ],

            'media.*.type' => [
                'required',
                'in:image,video',
            ],

            'media.*.file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,webm,mov',
                'max:51200',
            ],

            'media.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'media.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ], [
            'title.required' =>
                'El título de la ruta es obligatorio.',

            'media.array' =>
                'Los archivos enviados no son válidos.',

            'media.max' =>
                'Puedes subir un máximo de 50 archivos por carga.',

            'media.*.file.file' =>
                'Todos los archivos enviados deben ser válidos.',

            'media.*.file.mimes' =>
                'Los archivos deben ser JPG, JPEG, PNG, WEBP, MP4, WEBM o MOV.',

            'media.*.file.max' =>
                'Cada archivo puede pesar como máximo 50 MB.',
        ]);

        $route = Route::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $mediaInput = $request->input(
            'media',
            []
        );

        $mediaFiles = $request->file(
            'media',
            []
        );

        foreach (
            $mediaFiles as $index => $mediaItem
        ) {
            if (
                ! isset($mediaItem['file']) ||
                ! $mediaItem['file']->isValid()
            ) {
                continue;
            }

            $mediaData =
                $mediaInput[$index] ?? [];

            $file =
                $mediaItem['file'];

            /*
             * Detectamos el tipo real por la extensión.
             * No dependemos del type enviado por Vue.
             */
            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $type = in_array(
                $extension,
                [
                    'mp4',
                    'webm',
                    'mov',
                    'm4v',
                ],
                true
            )
                ? 'video'
                : 'image';

            $directory = storage_path(
                $type === 'image'
                    ? "app/public/routes/{$route->id}/images"
                    : "app/public/routes/{$route->id}/videos"
            );

            if (! is_dir($directory)) {
                mkdir(
                    $directory,
                    0755,
                    true
                );
            }

            $filename =
                uniqid().
                '.'.
                $file->getClientOriginalExtension();

            $file->move(
                $directory,
                $filename
            );

            $path =
                $type === 'image'
                    ? "routes/{$route->id}/images/{$filename}"
                    : "routes/{$route->id}/videos/{$filename}";

            RouteMedia::create([
                'route_id' => $route->id,
                'type' => $type,
                'file' => $path,
                'title' =>
                    $mediaData['title'] ?? null,
                'sort_order' =>
                    $mediaData['sort_order']
                    ?? $index,
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ruta creada correctamente.',
        ]);

        return redirect()
            ->route('admin.routes.index');
    }

    /**
     * Mostrar una ruta.
     */
    public function show(
        Route $route
    ): Response {
        $route->load([
            'media' => function ($query) {
                $query
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        return Inertia::render(
            'admin/routes/Show',
            [
                'route' => $route,
            ]
        );
    }

    /**
     * Mostrar formulario para editar una ruta.
     */
    public function edit(
        Route $route
    ): Response {
        $route->load([
            'media' => function ($query) {
                $query
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        return Inertia::render(
            'admin/routes/Edit',
            [
                'route' => $route,
            ]
        );
    }

    /**
     * Actualizar una ruta.
     */
    public function update(
        Request $request,
        Route $route
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'media' => [
                'nullable',
                'array',
                'max:50',
            ],

            'media.*.file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,webm,mov',
                'max:51200',
            ],

            'media.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'media.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'remove_media' => [
                'nullable',
                'array',
            ],

            'remove_media.*' => [
                'integer',
            ],

            'media_order' => [
                'nullable',
                'array',
            ],

            'media_order.*.id' => [
                'required',
                'integer',
            ],

            'media_order.*.sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ], [
            'title.required' =>
                'El título de la ruta es obligatorio.',

            'media.*.file.file' =>
                'Todos los archivos enviados deben ser válidos.',

            'media.*.file.mimes' =>
                'Los archivos deben ser JPG, JPEG, PNG, WEBP, MP4, WEBM o MOV.',

            'media.*.file.max' =>
                'Cada archivo puede pesar como máximo 50 MB.',
        ]);

        /*
         * Actualizar información principal.
         */
        $route->update([
            'title' =>
                $validated['title'],

            'description' =>
                $validated['description'] ?? null,

            'is_active' =>
                $validated['is_active'] ?? true,

            'sort_order' =>
                $validated['sort_order'] ?? 0,
        ]);

        /*
         * Eliminar archivos multimedia
         * que fueron quitados desde Edit.vue.
         */
        $removeMedia =
            $validated['remove_media']
            ?? [];

        if (! empty($removeMedia)) {
            $mediaItems = RouteMedia::query()
                ->where('route_id', $route->id)
                ->whereIn(
                    'id',
                    $removeMedia
                )
                ->get();

            foreach ($mediaItems as $media) {
                if ($media->file) {
                    $filePath = storage_path(
                        'app/public/'.
                        $media->file
                    );

                    if (is_file($filePath)) {
                        unlink($filePath);
                    }
                }

                $media->delete();
            }
        }

        /*
         * Actualizar el orden de los
         * archivos existentes.
         */
        $mediaOrder =
            $validated['media_order']
            ?? [];

        if (! empty($mediaOrder)) {
            $mediaIds = $route
                ->media()
                ->pluck('id')
                ->map(
                    fn ($id) => (int) $id
                )
                ->toArray();

            foreach ($mediaOrder as $item) {
                $mediaId =
                    (int) $item['id'];

                if (
                    ! in_array(
                        $mediaId,
                        $mediaIds,
                        true
                    )
                ) {
                    continue;
                }

                RouteMedia::where(
                    'id',
                    $mediaId
                )
                    ->where(
                        'route_id',
                        $route->id
                    )
                    ->update([
                        'sort_order' =>
                            $item['sort_order'],
                    ]);
            }
        }

        /*
         * Agregar nuevos archivos.
         */
        $mediaInput =
            $request->input(
                'media',
                []
            );

        $mediaFiles =
            $request->file(
                'media',
                []
            );

        if (! empty($mediaFiles)) {
            $lastOrder = (int) $route
                ->media()
                ->max('sort_order');

            foreach (
                $mediaFiles as $index => $mediaItem
            ) {
                if (
                    ! isset(
                        $mediaItem['file']
                    ) ||
                    ! $mediaItem['file']->isValid()
                ) {
                    continue;
                }

                $mediaData =
                    $mediaInput[$index] ?? [];

                $file =
                    $mediaItem['file'];

                /*
                 * Detectar tipo real por extensión.
                 */
                $extension = strtolower(
                    $file->getClientOriginalExtension()
                );

                $type = in_array(
                    $extension,
                    [
                        'mp4',
                        'webm',
                        'mov',
                        'm4v',
                    ],
                    true
                )
                    ? 'video'
                    : 'image';

                $lastOrder++;

                $directory = storage_path(
                    $type === 'image'
                        ? "app/public/routes/{$route->id}/images"
                        : "app/public/routes/{$route->id}/videos"
                );

                if (! is_dir($directory)) {
                    mkdir(
                        $directory,
                        0755,
                        true
                    );
                }

                $filename =
                    uniqid().
                    '.'.
                    $file->getClientOriginalExtension();

                $file->move(
                    $directory,
                    $filename
                );

                $path =
                    $type === 'image'
                        ? "routes/{$route->id}/images/{$filename}"
                        : "routes/{$route->id}/videos/{$filename}";

                RouteMedia::create([
                    'route_id' =>
                        $route->id,

                    'type' =>
                        $type,

                    'file' =>
                        $path,

                    'title' =>
                        $mediaData['title']
                        ?? null,

                    'sort_order' =>
                        $mediaData['sort_order']
                        ?? $lastOrder,
                ]);
            }
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ruta actualizada correctamente.',
        ]);

        return redirect()
            ->route('admin.routes.index');
    }

    /**
     * Eliminar una ruta.
     */
    public function destroy(
        Route $route
    ): RedirectResponse {
        $route->load('media');

        foreach ($route->media as $media) {
            if ($media->file) {
                $filePath = storage_path(
                    'app/public/'.
                    $media->file
                );

                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $route->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ruta eliminada correctamente.',
        ]);

        return redirect()
            ->route('admin.routes.index');
    }

    /**
     * Actualizar el orden de las imágenes y videos.
     */
    public function updateOrder(
        Request $request,
        Route $route
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

        $mediaIds = $route
            ->media()
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            )
            ->toArray();

        foreach ($validated['items'] as $item) {
            if (
                ! in_array(
                    (int) $item['id'],
                    $mediaIds,
                    true
                )
            ) {
                continue;
            }

            RouteMedia::where(
                'id',
                $item['id']
            )
                ->where(
                    'route_id',
                    $route->id
                )
                ->update([
                    'sort_order' =>
                        $item['sort_order'],
                ]);
        }

        return back();
    }

    /**
     * Actualizar información de un archivo multimedia.
     */
    public function updateMedia(
        Request $request,
        Route $route,
        RouteMedia $media
    ): RedirectResponse {
        if (
            $media->route_id !==
            $route->id
        ) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $media->update([
            'title' =>
                $validated['title'] ?? null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'El archivo se actualizó correctamente.',
        ]);

        return back();
    }

    /**
     * Eliminar una imagen o video.
     */
    public function destroyMedia(
        Route $route,
        RouteMedia $media
    ): RedirectResponse {
        if (
            $media->route_id !==
            $route->id
        ) {
            abort(404);
        }

        if ($media->file) {
            $filePath = storage_path(
                'app/public/'.
                $media->file
            );

            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        $media->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'El archivo se eliminó correctamente.',
        ]);

        return back();
    }
}

