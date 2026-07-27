<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'low_stock_threshold_default' => ['required', 'integer', 'min:0'],
            'vat_registration_number' => ['nullable', 'string', 'max:100'],
            'company_logo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
