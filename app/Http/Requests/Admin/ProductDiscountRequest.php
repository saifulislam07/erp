<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductDiscountRequest extends FormRequest
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
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'applicable_to' => ['required', Rule::in(['all', 'client_agent', 'local'])],
            'status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $productId = $this->route('product')->id;
            $start = $this->start_date;
            $end = $this->end_date;

            $overlaps = ProductDiscount::where('product_id', $productId)
                ->where('applicable_to', $this->applicable_to)
                ->when($this->route('discount'), fn ($q) => $q->whereKeyNot($this->route('discount')->id))
                ->where(function ($q) use ($start) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $start);
                })
                ->when($end, fn ($q) => $q->where('start_date', '<=', $end))
                ->exists();

            if ($overlaps) {
                $validator->errors()->add('start_date', 'This discount period overlaps with an existing discount for the same audience.');
            }
        });
    }
}
