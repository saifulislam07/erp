<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'low_stock_threshold_default' => ['required', 'integer', 'min:0'],
            'vat_registration_number' => ['nullable', 'string', 'max:100'],
            'invoice_footer_note' => ['nullable', 'string', 'max:500'],
            'company_logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],

            'mail_mailer' => ['required', Rule::in(['smtp', 'log', 'array'])],
            // Host, port and sender only matter for SMTP — and are required
            // once that transport is chosen, or mail silently fails later.
            'mail_host' => ['nullable', 'required_if:mail_mailer,smtp', 'string', 'max:255'],
            'mail_port' => ['nullable', 'required_if:mail_mailer,smtp', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'mail_from_address' => ['nullable', 'required_if:mail_mailer,smtp', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],

            'mail_send_sale_invoice' => ['nullable', 'boolean'],
            'mail_send_order_updates' => ['nullable', 'boolean'],
            'mail_send_password_reset' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mail_host' => 'mail host',
            'mail_port' => 'mail port',
            'mail_from_address' => 'send-from address',
            'low_stock_threshold_default' => 'default low-stock level',
        ];
    }

    public function messages(): array
    {
        return [
            'mail_host.required_if' => 'An SMTP host is required when the transport is SMTP.',
            'mail_port.required_if' => 'An SMTP port is required when the transport is SMTP.',
            'mail_from_address.required_if' => 'A send-from address is required when the transport is SMTP.',
        ];
    }
}
