<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSponsorRequest extends FormRequest
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
                'nullable',
                'string',
                'max:255',
                'unique:sponsors,slug',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'website' => [
                'nullable',
                'string',
                'url',
                'max:255',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if (
            ! $this->filled('slug')
            && $this->filled('name')
        ) {
            $data['slug'] = Str::slug($this->input('name'));
        }

        if (
            $this->has('is_active')
            && is_string($this->input('is_active'))
        ) {
            $data['is_active'] = filter_var(
                $this->input('is_active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del patrocinador es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',

            'slug.unique' => 'El identificador del patrocinador ya está en uso.',
            'slug.max' => 'El identificador no puede superar los 255 caracteres.',

            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'El logo debe estar en formato JPG, JPEG, PNG o WEBP.',
            'logo.max' => 'El logo no puede superar los 5 MB.',

            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 255 caracteres.',

            'website.url' => 'Ingresa una dirección web válida.',
            'website.max' => 'La dirección web no puede superar los 255 caracteres.',

            'phone.max' => 'El teléfono no puede superar los 50 caracteres.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'slug' => 'identificador',
            'logo' => 'logo',
            'description' => 'descripción',
            'contact_name' => 'nombre de contacto',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'website' => 'sitio web',
            'is_active' => 'estado',
        ];
    }
}
