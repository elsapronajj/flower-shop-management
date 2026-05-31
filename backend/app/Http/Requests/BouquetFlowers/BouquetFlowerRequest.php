<?php

namespace App\Http\Requests\BouquetFlowers;

use Illuminate\Foundation\Http\FormRequest;

class BouquetFlowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bouquet_id' => ['required', 'exists:bouquets,id'],
            'flower_id' => ['required', 'exists:flowers,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
