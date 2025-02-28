<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRecurringTransferRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipient_email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->whereNot('id', $this->user()->id),
            ],
            'amount' => [
                'required',
                'integer',
                'min:1'
            ],
            'reason' => [
                'required',
                'string',
                'max:255'
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date'
            ],
            'frequency_days' => [
                'required',
                'integer',
                'min:1',
                'max:365'
            ]
        ];
    }
}
