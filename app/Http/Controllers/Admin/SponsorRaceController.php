<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRaceSponsorRequest;
use App\Models\Race;
use App\Models\RaceSponsor;
use App\Models\Sponsor;
use App\Models\SponsorPayment;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SponsorRaceController extends Controller
{
    /**
     * Mostrar formulario para asociar una carrera al patrocinador.
     */
    public function create(Sponsor $sponsor)
    {
        $races = Race::query()
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

        $associatedRaceIds = $sponsor
            ->raceSponsors()
            ->pluck('race_id');

        $races = $races
            ->filter(
                fn (Race $race) =>
                    ! $associatedRaceIds->contains($race->id)
            )
            ->values();

        return Inertia::render(
            'admin/sponsors/RaceCreate',
            [
                'sponsor' => $sponsor,
                'races' => $races,
            ]
        );
    }

    /**
     * Asociar el patrocinador a una carrera.
     */
    public function store(
        StoreRaceSponsorRequest $request,
        Sponsor $sponsor
    ) {
        $data = $request->validated();

        $exists = RaceSponsor::query()
            ->where('race_id', $data['race_id'])
            ->where('sponsor_id', $sponsor->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'race_id' =>
                    'Este patrocinador ya está asociado a la carrera seleccionada.',
            ]);
        }

        DB::transaction(function () use (
            $data,
            $sponsor
        ): void {
            RaceSponsor::create([
                'race_id' => $data['race_id'],
                'sponsor_id' => $sponsor->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'benefits' => $data['benefits'] ?? null,
                'sort_order' => 0,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' =>
                'El patrocinador se asoció correctamente a la carrera.',
        ]);

        return to_route(
            'admin.sponsors.show',
            $sponsor
        );
    }

    /**
     * Mostrar el detalle del patrocinio.
     */
    public function show(
        Sponsor $sponsor,
        RaceSponsor $raceSponsor
    ) {
        abort_unless(
            $raceSponsor->sponsor_id === $sponsor->id,
            404
        );

        $raceSponsor->load([
            'race',
            'payments',
        ]);

        $agreed = (float) $raceSponsor->amount;

        $paid = (float) $raceSponsor
            ->payments
            ->sum('amount');

        $remaining = max(
            0,
            $agreed - $paid
        );

        $payments = $raceSponsor
            ->payments
            ->map(
                function (SponsorPayment $payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => (float) $payment->amount,
                        'paid_at' => $payment->paid_at
                            ? $payment->paid_at->format('Y-m-d')
                            : null,
                        'payment_method' => $payment->payment_method,
                        'reference' => $payment->reference,
                        'notes' => $payment->notes,
                        'receipt' => $payment->receipt,
                    ];
                }
            )
            ->values();

        return Inertia::render(
            'admin/sponsors/RaceShow',
            [
                'sponsor' => $sponsor,

                'raceSponsor' => [
                    'id' => $raceSponsor->id,

                    'race' => [
                        'id' => $raceSponsor->race->id,
                        'name' => $raceSponsor->race->name,
                        'slug' => $raceSponsor->race->slug,
                        'event_date' =>
                            $raceSponsor->race->event_date,
                        'start_time' =>
                            $raceSponsor->race->start_time,
                        'end_time' =>
                            $raceSponsor->race->end_time,
                        'location' =>
                            $raceSponsor->race->location,
                    ],

                    'type' => $raceSponsor->type,

                    'amount' => $agreed,

                    'paid' => $paid,

                    'remaining' => $remaining,

                    'benefits' => $raceSponsor->benefits,

                    'is_active' => $raceSponsor->is_active,

                    'payments' => $payments,
                ],
            ]
        );
    }

    /**
     * Eliminar la asociación con una carrera.
     */
    public function destroy(
        Sponsor $sponsor,
        RaceSponsor $raceSponsor
    ) {
        abort_unless(
            $raceSponsor->sponsor_id === $sponsor->id,
            404
        );

        $raceSponsor->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' =>
                'La asociación con la carrera se eliminó correctamente.',
        ]);

        return back();
    }
}