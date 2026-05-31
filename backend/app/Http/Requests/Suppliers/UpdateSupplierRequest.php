<?php

namespace App\Http\Requests\Suppliers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($this->supplier)],
            'address' => ['nullable', 'string'],
            'specialty' => ['nullable', 'string', 'max:255'],
        ];
    }
}
