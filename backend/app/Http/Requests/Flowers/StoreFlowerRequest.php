<?php

namespace App\Http\Requests\Flowers;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'season' => ['nullable', 'string', 'max:255'],
            'lifespan_days' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:100'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'photo' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }
}
