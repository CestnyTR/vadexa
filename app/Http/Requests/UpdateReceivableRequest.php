<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReceivableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['upcoming', 'due_today', 'overdue', 'paid'])],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
