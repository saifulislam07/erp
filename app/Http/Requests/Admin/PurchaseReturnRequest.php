<?php

namespace App\Http\Requests\Admin;

use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PurchaseReturnRequest extends FormRequest
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
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $currentReturn = $this->route('return');
            $hasPositiveQuantity = false;

            foreach ($this->input('items', []) as $index => $row) {
                if ((float) ($row['quantity'] ?? 0) > 0) {
                    $hasPositiveQuantity = true;
                }

                $purchaseItem = PurchaseItem::find($row['purchase_item_id'] ?? null);

                if (! $purchaseItem) {
                    continue;
                }

                $alreadyReturned = PurchaseReturnItem::where('purchase_item_id', $purchaseItem->id)
                    ->when($currentReturn, fn ($q) => $q->where('purchase_return_id', '!=', $currentReturn->id))
                    ->sum('quantity');

                $remaining = $purchaseItem->quantity - $alreadyReturned;

                if ((float) ($row['quantity'] ?? 0) > $remaining) {
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
