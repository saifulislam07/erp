<?php

namespace App\Http\Requests\Admin;

use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The description comes from a rich-text editor and is rendered unescaped,
     * so strip anything executable before it reaches validation or the database.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('description')) {
            $this->merge(['description' => HtmlSanitizer::clean($this->input('description'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('parent_id', null)],
            'sub_category_id' => ['nullable', Rule::exists('categories', 'id')->where('parent_id', $this->category_id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'unit_id' => ['required', 'exists:units,id'],
            'mrp_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'vat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp,bmp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'primary_image' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
            'expire_alert_1month' => ['nullable', 'boolean'],
            'expire_alert_3month' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'sub_category_id' => 'sub-category',
            'unit_id' => 'unit',
            'mrp_price' => 'MRP',
            'vat_percentage' => 'VAT %',
            'min_stock_threshold' => 'low-stock alert level',
            'images.*' => 'image',
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'A product can have at most 8 images.',
            'images.*.max' => 'Each image must be 5 MB or smaller.',
            'sub_category_id.exists' => 'The selected sub-category does not belong to the chosen category.',
        ];
    }

    /**
     * Cross-field checks that only make sense once the individual rules pass.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // MRP is the ceiling: the panel must never offer a price above it.
            if (filled($this->mrp_price) && (float) $this->mrp_price < (float) $this->sale_price) {
                $validator->errors()->add(
                    'mrp_price',
                    'The MRP cannot be lower than the sale price.'
                );
            }
        });
    }
}
