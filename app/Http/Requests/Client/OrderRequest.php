<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
            'payment_method' => ['required', Rule::in(['cash_on_delivery', 'bank', 'mobile_banking'])],
            'payment_receipt' => ['required_if:payment_method,bank', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'transaction_reference' => ['required_if:payment_method,mobile_banking', 'nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
