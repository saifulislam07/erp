<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FeedbackRequest extends FormRequest
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
            'product_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'product_comment' => ['nullable', 'string'],
            'delivery_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'delivery_comment' => ['nullable', 'string'],
            'agent_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'agent_comment' => ['nullable', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->product_rating && ! $this->delivery_rating && ! $this->agent_rating) {
                $validator->errors()->add('product_rating', 'Please provide at least one rating.');
            }
        });
    }
}
