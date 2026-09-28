<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StoreRaceRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:races,slug',
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
                'nullable',
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
                'string',
                'in:draft,published,registration_open,registration_closed,finished,cancelled',
            ],

            'terms_and_conditions' => [
                'nullable',
                'string',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'distances' => [
                'required',
                'array',
                'min:1',
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

            'distances.*.prices' => [
                'required',
                'array',
                'min:1',
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
                'after_or_equal:distances.*.prices.*.starts_at',
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

            'distances.*.inclusions' => [
                'required',
                'array',
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

            'distances.*.categories' => [
                'required',
                'array',
                'min:1',
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
        ];
    }
}