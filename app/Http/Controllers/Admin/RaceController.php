<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRaceRequest;
use App\Http\Requests\Admin\UpdateRaceRequest;
use App\Models\Race;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RaceController extends Controller
{
    /**
     * Display a listing of the races.
     */
    public function index(): Response
    {
        $search = request('search');

        $races = Race::query()
            ->withCount([
                'distances' => fn ($query) => $query->where(
                    'is_active',
                    true
                ),
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'location',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'city',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'state',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->latest('event_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/races/Index', [
            'races' => $races,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new race.
     */
    public function create(): Response
    {
        return Inertia::render('admin/races/Create');
    }

    /**
     * Store a newly created race.
     */
    public function store(
        StoreRaceRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $distances = $data['distances'] ?? [];

        unset($data['distances']);

        /**
         * --------------------------------------------------------------------------
         * Banner
         * --------------------------------------------------------------------------
         */
        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');

            if ($banner && $banner->isValid()) {
                /**
                 * ----------------------------------------------------------------------
                 * Generar nombre único.
                 * ----------------------------------------------------------------------
                 */
                $filename =
                    uniqid().
                    '.'.
                    $banner->getClientOriginalExtension();

                /**
                 * ----------------------------------------------------------------------
                 * Asegurar que exista el directorio.
                 * ----------------------------------------------------------------------
                 */
                $directory = storage_path(
                    'app/public/races/banners'
                );

                if (! is_dir($directory)) {
                    mkdir(
                        $directory,
                        0755,
                        true
                    );
                }

                /**
                 * ----------------------------------------------------------------------
                 * Guardar físicamente.
                 * ----------------------------------------------------------------------
                 */
                $banner->move(
                    $directory,
                    $filename
                );

                /**
                 * ----------------------------------------------------------------------
                 * Guardar ruta en BD.
                 * ----------------------------------------------------------------------
                 */
                $data['banner'] =
                    'races/banners/'.$filename;
            }
        }

        /**
         * --------------------------------------------------------------------------
         * Carrera + configuración
         * --------------------------------------------------------------------------
         */
        $race = DB::transaction(function () use (
            $data,
            $distances
        ) {
            $race = Race::create($data);

            foreach ($distances as $distanceData) {
                $this->createDistance(
                    $race,
                    $distanceData
                );
            }

            return $race;
        });

        /**
         * --------------------------------------------------------------------------
         * Toast
         * --------------------------------------------------------------------------
         */
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'La carrera se creó correctamente.',
        ]);

        return to_route(
            'admin.races.show',
            $race
        );
    }

    /**
     * Display the specified race.
     */
    public function show(
        Race $race
    ): Response {
        $race->load([
            'distances.prices',
            'distances.inclusions',
            'distances.categories',
        ]);

        return Inertia::render('admin/races/Show', [
            'race' => $race,
        ]);
    }

    /**
     * Show the form for editing the specified race.
     */
    public function edit(
        Race $race
    ): Response {
        $race->load([
            'distances.prices',
            'distances.inclusions',
            'distances.categories',
        ]);

        return Inertia::render('admin/races/Edit', [
            'race' => $race,
        ]);
    }

    /**
     * Update the specified race.
     */
    public function update(
        UpdateRaceRequest $request,
        Race $race
    ): RedirectResponse {
        $data = $request->validated();

        $distances = $data['distances'] ?? [];

        unset($data['distances']);

        /**
         * --------------------------------------------------------------------------
         * Banner
         * --------------------------------------------------------------------------
         */
        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');

            if ($banner && $banner->isValid()) {
                /**
                 * ----------------------------------------------------------------------
                 * Eliminar banner anterior.
                 * ----------------------------------------------------------------------
                 */
                if ($race->banner) {
                    Storage::disk('public')->delete(
                        $race->banner
                    );

                    /**
                     * Por si el archivo fue guardado directamente
                     * en storage/app/public.
                     */
                    $oldBanner = storage_path(
                        'app/public/'.$race->banner
                    );

                    if (is_file($oldBanner)) {
                        unlink($oldBanner);
                    }
                }

                /**
                 * ----------------------------------------------------------------------
                 * Generar nombre único.
                 * ----------------------------------------------------------------------
                 */
                $filename =
                    uniqid().
                    '.'.
                    $banner->getClientOriginalExtension();

                /**
                 * ----------------------------------------------------------------------
                 * Asegurar que exista el directorio.
                 * ----------------------------------------------------------------------
                 */
                $directory = storage_path(
                    'app/public/races/banners'
                );

                if (! is_dir($directory)) {
                    mkdir(
                        $directory,
                        0755,
                        true
                    );
                }

                /**
                 * ----------------------------------------------------------------------
                 * Guardar físicamente.
                 * ----------------------------------------------------------------------
                 */
                $banner->move(
                    $directory,
                    $filename
                );

                /**
                 * ----------------------------------------------------------------------
                 * Guardar nueva ruta en BD.
                 * ----------------------------------------------------------------------
                 */
                $data['banner'] =
                    'races/banners/'.$filename;
            }
        } else {
            /**
             * Si no se envió un nuevo banner,
             * conservar el existente.
             */
            unset($data['banner']);
        }

        /**
         * --------------------------------------------------------------------------
         * Carrera + configuración
         * --------------------------------------------------------------------------
         */
        DB::transaction(function () use (
            $race,
            $data,
            $distances
        ) {
            $race->update($data);

            $this->updateDistances(
                $race,
                $distances
            );
        });

        /**
         * --------------------------------------------------------------------------
         * Toast
         * --------------------------------------------------------------------------
         */
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'La carrera se actualizó correctamente.',
        ]);

        return to_route(
            'admin.races.show',
            $race
        );
    }

    /**
     * Remove the specified race.
     */
    public function destroy(
        Race $race
    ): RedirectResponse {
        /**
         * --------------------------------------------------------------------------
         * Eliminar banner.
         * --------------------------------------------------------------------------
         */
        if ($race->banner) {
            Storage::disk('public')->delete(
                $race->banner
            );

            $banner = storage_path(
                'app/public/'.$race->banner
            );

            if (is_file($banner)) {
                unlink($banner);
            }
        }

        $race->delete();

        /**
         * --------------------------------------------------------------------------
         * Toast
         * --------------------------------------------------------------------------
         */
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'La carrera se eliminó correctamente.',
        ]);

        return to_route(
            'admin.races.index'
        );
    }

    /**
     * Create a distance and its configuration.
     */
    private function createDistance(
        Race $race,
        array $distanceData
    ): void {
        $prices = $distanceData['prices'] ?? [];
        $inclusions = $distanceData['inclusions'] ?? [];
        $categories = $distanceData['categories'] ?? [];

        unset(
            $distanceData['id'],
            $distanceData['prices'],
            $distanceData['inclusions'],
            $distanceData['categories']
        );

        $distance = $race->distances()->create(
            $distanceData
        );

        foreach ($prices as $priceData) {
            unset($priceData['id']);

            $distance->prices()->create(
                $priceData
            );
        }

        foreach ($inclusions as $inclusionData) {
            unset($inclusionData['id']);

            $distance->inclusions()->create(
                $inclusionData
            );
        }

        foreach ($categories as $categoryData) {
            unset($categoryData['id']);

            $distance->categories()->create(
                $categoryData
            );
        }
    }

    /**
     * Update distances and their configuration.
     */
    private function updateDistances(
        Race $race,
        array $distances
    ): void {
        $existingDistances = $race->distances()
            ->get()
            ->keyBy('id');

        $receivedDistanceIds = [];

        foreach ($distances as $distanceData) {
            $distanceId = $distanceData['id'] ?? null;

            $prices = $distanceData['prices'] ?? [];
            $inclusions = $distanceData['inclusions'] ?? [];
            $categories = $distanceData['categories'] ?? [];

            unset(
                $distanceData['id'],
                $distanceData['prices'],
                $distanceData['inclusions'],
                $distanceData['categories']
            );

            /**
             * ----------------------------------------------------------------------
             * Distancia existente.
             * ----------------------------------------------------------------------
             */
            if (
                $distanceId &&
                $existingDistances->has($distanceId)
            ) {
                $distance = $existingDistances->get(
                    $distanceId
                );

                $distance->update(
                    $distanceData
                );
            } else {
                /**
                 * ------------------------------------------------------------------
                 * Nueva distancia.
                 * ------------------------------------------------------------------
                 */
                $distance = $race->distances()->create(
                    $distanceData
                );
            }

            $receivedDistanceIds[] = $distance->id;

            /**
             * ----------------------------------------------------------------------
             * Precios.
             * ----------------------------------------------------------------------
             */
            $this->updatePrices(
                $distance,
                $prices
            );

            /**
             * ----------------------------------------------------------------------
             * Inclusiones.
             * ----------------------------------------------------------------------
             */
            $this->updateInclusions(
                $distance,
                $inclusions
            );

            /**
             * ----------------------------------------------------------------------
             * Categorías.
             * ----------------------------------------------------------------------
             */
            $this->updateCategories(
                $distance,
                $categories
            );
        }

        /**
         * --------------------------------------------------------------------------
         * Eliminar distancias que ya no vienen
         * en el formulario.
         * --------------------------------------------------------------------------
         */
        $distancesToDelete = $existingDistances
            ->keys()
            ->diff($receivedDistanceIds);

        if ($distancesToDelete->isNotEmpty()) {
            $race->distances()
                ->whereIn(
                    'id',
                    $distancesToDelete
                )
                ->delete();
        }
    }

    /**
     * Update prices.
     */
    private function updatePrices(
        $distance,
        array $prices
    ): void {
        $existingPrices = $distance->prices()
            ->get()
            ->keyBy('id');

        $receivedPriceIds = [];

        foreach ($prices as $priceData) {
            $priceId = $priceData['id'] ?? null;

            unset($priceData['id']);

            if (
                $priceId &&
                $existingPrices->has($priceId)
            ) {
                $price = $existingPrices->get(
                    $priceId
                );

                $price->update(
                    $priceData
                );
            } else {
                $price = $distance->prices()->create(
                    $priceData
                );
            }

            $receivedPriceIds[] = $price->id;
        }

        $pricesToDelete = $existingPrices
            ->keys()
            ->diff($receivedPriceIds);

        if ($pricesToDelete->isNotEmpty()) {
            $distance->prices()
                ->whereIn(
                    'id',
                    $pricesToDelete
                )
                ->delete();
        }
    }

    /**
     * Update inclusions.
     */
    private function updateInclusions(
        $distance,
        array $inclusions
    ): void {
        $existingInclusions = $distance->inclusions()
            ->get()
            ->keyBy('id');

        $receivedInclusionIds = [];

        foreach ($inclusions as $inclusionData) {
            $inclusionId = $inclusionData['id'] ?? null;

            unset($inclusionData['id']);

            if (
                $inclusionId &&
                $existingInclusions->has($inclusionId)
            ) {
                $inclusion = $existingInclusions->get(
                    $inclusionId
                );

                $inclusion->update(
                    $inclusionData
                );
            } else {
                $inclusion = $distance->inclusions()->create(
                    $inclusionData
                );
            }

            $receivedInclusionIds[] = $inclusion->id;
        }

        $inclusionsToDelete = $existingInclusions
            ->keys()
            ->diff($receivedInclusionIds);

        if ($inclusionsToDelete->isNotEmpty()) {
            $distance->inclusions()
                ->whereIn(
                    'id',
                    $inclusionsToDelete
                )
                ->delete();
        }
    }

    /**
     * Update categories.
     */
    private function updateCategories(
        $distance,
        array $categories
    ): void {
        $existingCategories = $distance->categories()
            ->get()
            ->keyBy('id');

        $receivedCategoryIds = [];

        foreach ($categories as $categoryData) {
            $categoryId = $categoryData['id'] ?? null;

            unset($categoryData['id']);

            if (
                $categoryId &&
                $existingCategories->has($categoryId)
            ) {
                $category = $existingCategories->get(
                    $categoryId
                );

                $category->update(
                    $categoryData
                );
            } else {
                $category = $distance->categories()->create(
                    $categoryData
                );
            }

            $receivedCategoryIds[] = $category->id;
        }

        $categoriesToDelete = $existingCategories
            ->keys()
            ->diff($receivedCategoryIds);

        if ($categoriesToDelete->isNotEmpty()) {
            $distance->categories()
                ->whereIn(
                    'id',
                    $categoriesToDelete
                )
                ->delete();
        }
    }
}