<?php

namespace App\Http\Requests\Admin;

use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $sale = $this->route('sale');

        return $sale ? $this->user()->can('update', $sale) : $this->user()->can('create', Sale::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_type' => ['required', Rule::in(['local', 'client_agent'])],
            'customer_id' => ['required_if:customer_type,client_agent', 'nullable', 'exists:clients,id'],
            'customer_name' => ['required_if:customer_type,local', 'nullable', 'string', 'max:255'],
            'sale_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'mobile_banking'])],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.store_id' => ['required', 'exists:stores,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();

            if ($this->customer_type === 'client_agent' && ! $user->can('sellToClientAgent', Sale::class)) {
                $validator->errors()->add('customer_type', 'You are not permitted to sell to client/agent customers.');
            }

            $stockService = app(StockService::class);
            $sale = $this->route('sale');

            foreach ($this->input('items', []) as $index => $row) {
                if (empty($row['product_id']) || empty($row['store_id'])) {
                    continue;
                }

                $available = $stockService->getAvailableStock((int) $row['product_id'], (int) $row['store_id']);

                if ($sale) {
                    $existingQty = $sale->items()
                        ->where('product_id', $row['product_id'])
                        ->where('store_id', $row['store_id'])
                        ->sum('quantity');
                    $available += $existingQty;
                }

                if ((float) ($row['quantity'] ?? 0) > $available) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Only {$available} units available in stock for this product/store."
                    );
                }
            }
        });
    }
}
