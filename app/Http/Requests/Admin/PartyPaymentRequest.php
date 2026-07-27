<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'method' => ['required', Rule::in(['cash', 'bank', 'mobile_banking'])],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_date.before_or_equal' => 'A payment cannot be dated in the future.',
            'amount.min' => 'Enter an amount greater than zero.',
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_date' => 'payment date',
            'reference' => 'reference number',
        ];
    }
}
