<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceDiscountParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RaceDiscountController extends Controller
{
    /**
     * Pantalla de descuentos de la carrera.
     */
    public function index(Race $race): Response
    {
        $participants = $race->discountParticipants()
            ->get(['folio', 'first_name', 'last_name'])
            ->sortBy('folio', SORT_NATURAL)
            ->values();

        return Inertia::render('admin/races/discounts/Index', [
            'race' => [
                'id' => $race->id,
                'name' => $race->name,
                'slug' => $race->slug,
                'discount_registration_url' => $race->discount_registration_url,
                'discount_amount' => $race->discount_amount,
            ],
            'participants' => $participants,
        ]);
    }

    /**
     * Guardar el enlace de registro con descuento y el monto.
     */
    public function updateUrl(
        Request $request,
        Race $race,
    ): RedirectResponse {
        $validated = $request->validate([
            'discount_registration_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999',
            ],
        ], [
            'discount_amount.*' => 'Ingresa un precio válido.',
        ]);

        $race->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Descuento guardado.',
        ]);

        return back();
    }

    /**
     * Importar participantes (folio, nombres y apellidos ya extraídos
     * del Excel en el navegador).
     */
    public function import(
        Request $request,
        Race $race,
    ): RedirectResponse {
        $validated = $request->validate([
            'participants' => ['required', 'array', 'min:1', 'max:20000'],
            'participants.*.folio' => ['required', 'string', 'max:50'],
            'participants.*.first_name' => ['required', 'string', 'max:255'],
            'participants.*.last_name' => ['required', 'string', 'max:255'],
            'replace' => ['boolean'],
        ]);

        // Un folio por carrera: si se repite en el archivo, gana el último.
        $participants = collect($validated['participants'])
            ->map(fn (array $row) => [
                'folio' => RaceDiscountParticipant::normalizeFolio($row['folio']),
                'first_name' => trim(preg_replace('/\s+/', ' ', $row['first_name'])),
                'last_name' => trim(preg_replace('/\s+/', ' ', $row['last_name'])),
            ])
            ->keyBy('folio')
            ->values();

        DB::transaction(function () use ($race, $participants, $validated) {
            if ($validated['replace'] ?? false) {
                $race->discountParticipants()->delete();
            }

            $now = now();

            $participants->chunk(500)->each(function ($chunk) use ($race, $now) {
                DB::table('race_discount_participants')->upsert(
                    $chunk->map(fn (array $row) => [
                        'race_id' => $race->id,
                        ...$row,
                        'name_key' => RaceDiscountParticipant::fullNameKey(
                            $row['first_name'],
                            $row['last_name'],
                        ),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                    ['race_id', 'folio'],
                    ['first_name', 'last_name', 'name_key', 'updated_at'],
                );
            });
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Se importaron {$participants->count()} participante(s).",
        ]);

        return back();
    }

    /**
     * Eliminar toda la lista de participantes.
     */
    public function destroy(Race $race): RedirectResponse
    {
        $race->discountParticipants()->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Lista de participantes eliminada.',
        ]);

        return back();
    }
}
