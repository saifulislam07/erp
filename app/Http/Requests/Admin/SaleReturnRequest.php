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
            // Only a refund that actually pays out needs a method. `required_with`
            // cannot express this: a zero refund is still "present", so it would
            // demand a method for every goods-only return.
            'refund_method' => [
                'nullable',
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
