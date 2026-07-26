<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('salaries', 'user_id')->where('month', $this->month),
            ],
            'month' => ['required', 'string', 'date_format:Y-m'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['cash', 'bank'])],
            'note' => ['nullable', 'string'],
        ];
    }
}
