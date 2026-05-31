<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
            'order_date' => ['sometimes', 'required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string'],
            'card_message' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'completed', 'cancelled'])],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.flower_id' => ['nullable', 'required_without:items.*.bouquet_id', 'exists:flowers,id'],
            'items.*.bouquet_id' => ['nullable', 'required_without:items.*.flower_id', 'exists:bouquets,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ];
    }
}
