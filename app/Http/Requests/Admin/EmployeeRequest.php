<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmployeeRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('employee')),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            // Any role except "Admin" — admins are not created from this UI.
            'role' => [
                'required',
                Rule::exists('roles', 'name')->where(
                    fn ($query) => $query->where('guard_name', 'web')->where('name', '!=', 'Admin')
                ),
            ],
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = ['required', 'string', Password::defaults()];
        }

        return $rules;
    }
}
