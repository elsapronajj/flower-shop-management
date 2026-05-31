<?php

namespace App\Http\Requests\Occasions;

use Illuminate\Foundation\Http\FormRequest;

class OccasionRequest extends FormRequest
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
            'event_date' => ['nullable', 'date'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
