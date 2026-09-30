<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSponsorPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'paid_at' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'payment_method' => [
                'nullable',
                'string',
                'max:100',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'receipt' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'El monto del pago es obligatorio.',

            'amount.numeric' => 'El monto del pago debe ser numérico.',

            'amount.min' => 'El monto del pago debe ser mayor a cero.',

            'paid_at.required' => 'La fecha del pago es obligatoria.',

            'paid_at.date' => 'La fecha del pago no es válida.',

            'paid_at.before_or_equal' => 'La fecha del pago no puede ser futura.',

            'receipt.file' => 'El comprobante no es válido.',

            'receipt.mimes' => 'El comprobante debe ser JPG, PNG, JPEG o PDF.',

            'receipt.max' => 'El comprobante no puede superar los 5 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'monto',

            'paid_at' => 'fecha de pago',

            'payment_method' => 'método de pago',

            'reference' => 'referencia',

            'notes' => 'notas',

            'receipt' => 'comprobante',
        ];
    }
}
