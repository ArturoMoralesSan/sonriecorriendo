<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSponsorRequest;
use App\Http\Requests\Admin\UpdateSponsorRequest;
use App\Models\Race;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SponsorController extends Controller
{
    /**
     * Display a listing of sponsors.
     */
    public function index(): Response
    {
        $search = request('search');

        $sponsors = Sponsor::query()
            ->withCount('raceSponsors')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/sponsors/Index', [
            'sponsors' => $sponsors,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new sponsor.
     */
    public function create(): Response
    {
        return Inertia::render('admin/sponsors/Create');
    }

    /**
     * Store a newly created sponsor.
     */
    public function store(StoreSponsorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /*
         * El archivo no debe llegar directamente al modelo.
         * Lo procesamos por separado.
         */
        unset($data['logo']);

        $logo = $request->file('logo');

        if ($logo && $logo->isValid()) {
            $filename = uniqid('', true)
                . '.'
                . $logo->getClientOriginalExtension();

            $directory = storage_path(
                'app/public/sponsors'
            );

            if (! is_dir($directory)) {
                mkdir(
                    $directory,
                    0755,
                    true
                );
            }

            $logo->move(
                $directory,
                $filename
            );

            /*
             * Guardamos la ruta relativa que utilizará
             * el disco public.
             */
            $data['logo'] =
                'sponsors/' . $filename;
        } else {
            $data['logo'] = null;
        }

        Sponsor::create($data);

        return redirect()
            ->route('admin.sponsors.index')
            ->with(
                'success',
                'El patrocinador se creó correctamente.'
            );
    }

    /**
     * Display the specified sponsor.
     */
    public function show(Sponsor $sponsor): Response
    {
        $sponsor->load([
            'raceSponsors.race',
        ]);

        $associatedRaceIds = $sponsor
            ->raceSponsors()
            ->pluck('race_id');

        $races = Race::query()
            ->whereNotIn('id', $associatedRaceIds)
            ->orderByDesc('event_date')
            ->get([
                'id',
                'name',
                'slug',
                'event_date',
                'start_time',
                'end_time',
                'location',
            ]);

        return Inertia::render(
            'admin/sponsors/Show',
            [
                'sponsor' => $sponsor,
                'races' => $races,
            ]
        );
    }

    /**
     * Show the form for editing the specified sponsor.
     */
    public function edit(Sponsor $sponsor): Response
    {
        return Inertia::render(
            'admin/sponsors/Edit',
            [
                'sponsor' => $sponsor,
            ]
        );
    }

    /**
     * Update the specified sponsor.
     */
    public function update(
        UpdateSponsorRequest $request,
        Sponsor $sponsor
    ): RedirectResponse {
        $data = $request->validated();

        /*
         * Estos campos pertenecen al formulario,
         * pero no son columnas que debamos mandar
         * directamente al modelo.
         */
        unset(
            $data['logo'],
            $data['remove_logo']
        );

        $logo = $request->file('logo');

        /*
         * =====================================================
         * NUEVO LOGO
         * =====================================================
         */
        if ($logo && $logo->isValid()) {
            /*
             * Eliminamos el logo anterior solamente después
             * de confirmar que tenemos un nuevo archivo válido.
             */
            if ($sponsor->logo) {
                Storage::disk('public')->delete(
                    $sponsor->logo
                );
            }

            $filename = uniqid('', true)
                . '.'
                . $logo->getClientOriginalExtension();

            $directory = storage_path(
                'app/public/sponsors'
            );

            if (! is_dir($directory)) {
                mkdir(
                    $directory,
                    0755,
                    true
                );
            }

            $logo->move(
                $directory,
                $filename
            );

            /*
             * Aquí queda guardado en la columna logo
             * solamente el nombre/ruta del archivo.
             */
            $data['logo'] =
                'sponsors/' . $filename;
        }

        /*
         * =====================================================
         * ELIMINAR LOGO
         * =====================================================
         */
        elseif ($request->boolean('remove_logo')) {
            if ($sponsor->logo) {
                Storage::disk('public')->delete(
                    $sponsor->logo
                );
            }

            $data['logo'] = null;
        }

        /*
         * Si no se seleccionó un nuevo logo y tampoco se pidió
         * eliminarlo, NO tocamos la columna logo.
         */
        $sponsor->update($data);

        return redirect()
            ->route('admin.sponsors.index')
            ->with(
                'success',
                'El patrocinador se actualizó correctamente.'
            );
    }

    /**
     * Remove the specified sponsor.
     */
    public function destroy(
        Sponsor $sponsor
    ): RedirectResponse {
        if ($sponsor->logo) {
            Storage::disk('public')->delete(
                $sponsor->logo
            );
        }

        $sponsor->delete();

        return redirect()
            ->route('admin.sponsors.index')
            ->with(
                'success',
                'El patrocinador se eliminó correctamente.'
            );
    }
}