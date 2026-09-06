<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required', 'date', 'before_or_equal:today'],
            'reason' => ['required', 'string', 'max:1000'],
            'restock' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'refund_method' => [
                'nullable',
                // Not `required_with:refund_amount`: prepareForValidation below
                // normalises a blank box to 0, and 0 still counts as "present",
                // so that rule demanded a method for every refund-free return.
                Rule::requiredIf(fn () => (float) $this->input('refund_amount') > 0),
                Rule::in(['cash', 'bank', 'mobile_banking']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        // An empty refund box and a zero refund mean the same thing; normalise
        // so `required_with` does not demand a method for a zero refund.
        if (! filled($this->input('refund_amount')) || (float) $this->input('refund_amount') <= 0) {
            $this->merge(['refund_amount' => 0, 'refund_method' => null]);
        }
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Enter a quantity for at least one item.',
            'return_date.before_or_equal' => 'A return cannot be dated in the future.',
            'refund_method.required' => 'Choose how the refund was paid out.',
        ];
    }
}
