<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRaceSponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'race_id' => [
                'required',
                'integer',
                'exists:races,id',
            ],

            'type' => [
                'required',
                'string',
                'max:100',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'benefits' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (
            $this->has('is_active')
            && is_string($this->input('is_active'))
        ) {
            $this->merge([
                'is_active' => filter_var(
                    $this->input('is_active'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'race_id.required' =>
                'La carrera es obligatoria.',

            'race_id.exists' =>
                'La carrera seleccionada no existe.',

            'type.required' =>
                'El tipo de patrocinio es obligatorio.',

            'amount.required' =>
                'El monto acordado es obligatorio.',

            'amount.numeric' =>
                'El monto acordado debe ser numérico.',

            'amount.min' =>
                'El monto acordado no puede ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'race_id' => 'carrera',
            'type' => 'tipo de patrocinio',
            'amount' => 'monto acordado',
            'benefits' => 'beneficios',
            'is_active' => 'estado',
        ];
    }
}