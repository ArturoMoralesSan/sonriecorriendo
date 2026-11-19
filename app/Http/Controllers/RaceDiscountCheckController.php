<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceDiscountParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RaceDiscountCheckController extends Controller
{
    private const PUBLIC_STATUSES = [
        'published',
        'registration_open',
        'registration_closed',
        'finished',
    ];

    /**
     * Verificar si un folio, un nombre completo o ambos tienen
     * descuento en la carrera.
     *
     * Solo responde si aplica o no (y el enlace cuando aplica);
     * nunca expone datos de otros participantes.
     */
    public function __invoke(
        Request $request,
        string $slug,
    ): JsonResponse {
        $data = $this->validatedData($request);

        $race = Race::query()
            ->where('slug', $slug)
            ->whereIn('status', self::PUBLIC_STATUSES)
            ->firstOrFail();

        $eligible = filled($race->discount_registration_url)
            && $this->raceMatches($race, $data);

        return response()->json([
            'eligible' => $eligible,
            'url' => $eligible
                ? $race->discount_registration_url
                : null,
        ]);
    }

    /**
     * Página pública para verificar descuentos.
     */
    public function page(): Response
    {
        return Inertia::render('Discounts');
    }

    /**
     * Buscar en todas las carreras con inscripción abierta las que
     * tengan descuento para el folio y/o nombre completo. Solo
     * devuelve las carreras que coinciden; nunca expone datos de
     * otros participantes.
     */
    public function search(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);

        $races = Race::query()
            ->whereIn('status', ['published', 'registration_open'])
            ->whereNotNull('discount_registration_url')
            ->where('discount_registration_url', '!=', '')
            ->whereHas(
                'discountParticipants',
                fn ($query) => $this->applyFilters($query, $data),
            )
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'slug', 'discount_registration_url', 'discount_amount']);

        return response()->json([
            'races' => $races->map(fn (Race $race) => [
                'name' => $race->name,
                'slug' => $race->slug,
                'url' => $race->discount_registration_url,
                'amount' => $race->discount_amount,
            ])->values(),
        ]);
    }

    /**
     * @param  array{folio: ?string, name_key: ?string}  $data
     */
    private function raceMatches(Race $race, array $data): bool
    {
        return $this->applyFilters($race->discountParticipants(), $data)
            ->exists();
    }

    /**
     * Folio (exacto), nombre completo (sin acentos, mayúsculas ni
     * espacios extra) o ambos. Si vienen los dos, deben ser de la
     * misma persona.
     *
     * @param  array{folio: ?string, name_key: ?string}  $data
     */
    private function applyFilters($query, array $data)
    {
        return $query
            ->when(
                $data['folio'],
                fn ($q, $folio) => $q->where('folio', $folio),
            )
            ->when(
                $data['name_key'],
                fn ($q, $nameKey) => $q->where('name_key', $nameKey),
            );
    }

    /**
     * Requiere folio, nombre completo o ambos.
     *
     * @return array{folio: ?string, name_key: ?string}
     */
    private function validatedData(Request $request): array
    {
        $folio = RaceDiscountParticipant::normalizeFolio($request->input('folio'));
        $fullName = trim((string) $request->input('full_name'));

        $request->merge([
            'folio' => $folio === '' ? null : $folio,
            'full_name' => $fullName === '' ? null : $fullName,
        ]);

        $request->validate([
            'folio' => ['nullable', 'required_without:full_name', 'string', 'max:50'],
            'full_name' => ['nullable', 'required_without:folio', 'string', 'max:255'],
        ], [
            'folio.required_without' => 'Ingresa tu número de folio o tu nombre completo.',
            'full_name.required_without' => 'Ingresa tu número de folio o tu nombre completo.',
            'folio.*' => 'El folio no es válido.',
            'full_name.*' => 'El nombre no es válido.',
        ]);

        $nameKey = RaceDiscountParticipant::normalizeName($request->input('full_name'));

        return [
            'folio' => $request->input('folio'),
            'name_key' => $nameKey === '' ? null : $nameKey,
        ];
    }
}
