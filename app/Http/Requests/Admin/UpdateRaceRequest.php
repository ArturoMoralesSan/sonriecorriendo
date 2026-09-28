<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $raceId = $this->route('race')->id;

        return [
            /**
             * |--------------------------------------------------------------------------
             * | Carrera
             * |--------------------------------------------------------------------------
             */
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('races', 'slug')->ignore($raceId),
            ],

            'results_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'event_date' => [
                'required',
                'date',
            ],

            'start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'country' => [
                'required',
                'string',
                'max:100',
            ],

            'banner' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'registration_opens_at' => [
                'nullable',
                'date',
            ],

            'registration_closes_at' => [
                'nullable',
                'date',
                'after_or_equal:registration_opens_at',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                    'registration_open',
                    'registration_closed',
                    'finished',
                    'cancelled',
                ]),
            ],

            'terms_and_conditions' => [
                'nullable',
                'string',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            /**
             * |--------------------------------------------------------------------------
             * | Distancias
             * |--------------------------------------------------------------------------
             */
            'distances' => [
                'required',
                'array',
                'min:1',
            ],

            'distances.*.id' => [
                'nullable',
                'integer',
            ],

            'distances.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'distances.*.distance' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'distances.*.unit' => [
                'required',
                'string',
                'in:km,m',
            ],

            'distances.*.start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'distances.*.capacity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'distances.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'distances.*.is_active' => [
                'boolean',
            ],

            /**
             * |--------------------------------------------------------------------------
             * | Precios
             * |--------------------------------------------------------------------------
             */
            'distances.*.prices' => [
                'required',
                'array',
                'min:1',
            ],

            'distances.*.prices.*.id' => [
                'nullable',
                'integer',
            ],

            'distances.*.prices.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'distances.*.prices.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'distances.*.prices.*.starts_at' => [
                'nullable',
                'date',
            ],

            'distances.*.prices.*.ends_at' => [
                'nullable',
                'date',
            ],

            'distances.*.prices.*.capacity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'distances.*.prices.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'distances.*.prices.*.is_active' => [
                'boolean',
            ],

            /**
             * |--------------------------------------------------------------------------
             * | Inclusiones
             * |--------------------------------------------------------------------------
             */
            'distances.*.inclusions' => [
                'required',
                'array',
            ],

            'distances.*.inclusions.*.id' => [
                'nullable',
                'integer',
            ],

            'distances.*.inclusions.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'distances.*.inclusions.*.description' => [
                'nullable',
                'string',
            ],

            'distances.*.inclusions.*.type' => [
                'required',
                'string',
                'max:50',
            ],

            'distances.*.inclusions.*.included' => [
                'boolean',
            ],

            'distances.*.inclusions.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /**
             * |--------------------------------------------------------------------------
             * | Categorías
             * |--------------------------------------------------------------------------
             */
            'distances.*.categories' => [
                'required',
                'array',
                'min:1',
            ],

            'distances.*.categories.*.id' => [
                'nullable',
                'integer',
            ],

            'distances.*.categories.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'distances.*.categories.*.description' => [
                'nullable',
                'string',
            ],

            'distances.*.categories.*.min_age' => [
                'nullable',
                'integer',
                'min:0',
                'max:120',
            ],

            'distances.*.categories.*.max_age' => [
                'nullable',
                'integer',
                'min:0',
                'max:120',
            ],

            'distances.*.categories.*.gender' => [
                'required',
                'string',
                'in:mixed,male,female',
            ],

            'distances.*.categories.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'distances.*.categories.*.is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la carrera es obligatorio.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'El slug ya está en uso.',
            'slug.alpha_dash' => 'El slug solo puede contener letras, números, guiones y guiones bajos.',
            'results_url.url' => 'El enlace de resultados debe ser una URL válida.',
            'event_date.required' => 'La fecha de la carrera es obligatoria.',
            'end_time.after' => 'La hora de finalización debe ser posterior a la hora de inicio.',
            'country.required' => 'El país es obligatorio.',
            'registration_closes_at.after_or_equal' =>
                'El cierre de registros debe ser posterior o igual a la apertura.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'distances.required' => 'Debe existir al menos una distancia.',
            'distances.min' => 'Debe existir al menos una distancia.',
            'distances.*.name.required' =>
                'El nombre de la distancia es obligatorio.',
            'distances.*.distance.required' =>
                'La distancia es obligatoria.',
            'distances.*.distance.numeric' =>
                'La distancia debe ser un número.',
            'distances.*.unit.required' =>
                'La unidad de la distancia es obligatoria.',
            'distances.*.unit.in' =>
                'La unidad de la distancia debe ser km o m.',
            'distances.*.prices.required' =>
                'Cada distancia debe tener al menos un precio.',
            'distances.*.prices.min' =>
                'Cada distancia debe tener al menos un precio.',
            'distances.*.prices.*.name.required' =>
                'El nombre del precio es obligatorio.',
            'distances.*.prices.*.price.required' =>
                'El precio es obligatorio.',
            'distances.*.prices.*.price.numeric' =>
                'El precio debe ser un número.',
            'distances.*.inclusions.*.name.required' =>
                'El nombre de la inclusión es obligatorio.',
            'distances.*.inclusions.*.type.required' =>
                'El tipo de inclusión es obligatorio.',
            'distances.*.categories.required' =>
                'Cada distancia debe tener al menos una categoría.',
            'distances.*.categories.min' =>
                'Cada distancia debe tener al menos una categoría.',
            'distances.*.categories.*.name.required' =>
                'El nombre de la categoría es obligatorio.',
            'distances.*.categories.*.gender.required' =>
                'El género de la categoría es obligatorio.',
            'distances.*.categories.*.gender.in' =>
                'El género seleccionado no es válido.',
        ];
    }
}