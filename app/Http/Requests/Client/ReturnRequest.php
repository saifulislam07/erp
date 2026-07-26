<?php

namespace App\Http\Requests\Client;

use App\Models\OrderItem;
use App\Models\ReturnItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReturnRequest extends FormRequest
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
            'return_type_id' => ['required', 'exists:return_types,id'],
            'reason' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasPositiveQuantity = false;

            foreach ($this->input('items', []) as $index => $row) {
                $quantity = (float) ($row['quantity'] ?? 0);

                if ($quantity > 0) {
                    $hasPositiveQuantity = true;
                }

                $orderItem = OrderItem::find($row['order_item_id'] ?? null);

                if (! $orderItem) {
                    continue;
                }

                $alreadyReturned = ReturnItem::where('order_item_id', $orderItem->id)
                    ->whereHas('orderReturn', fn ($q) => $q->where('status', '!=', 'rejected'))
                    ->sum('quantity');

                $remaining = $orderItem->quantity - $alreadyReturned;

                if ($quantity > $remaining) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Return quantity exceeds the remaining returnable quantity ({$remaining})."
                    );
                }
            }

            if (! $hasPositiveQuantity) {
                $validator->errors()->add('items', 'Enter a return quantity for at least one product.');
            }
        });
    }
}
