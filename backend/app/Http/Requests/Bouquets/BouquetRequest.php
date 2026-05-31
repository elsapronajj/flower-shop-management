<?php

namespace App\Http\Requests\Bouquets;

use Illuminate\Foundation\Http\FormRequest;

class BouquetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'size' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }
}
