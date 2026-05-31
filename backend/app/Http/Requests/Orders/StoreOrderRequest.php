<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string'],
            'card_message' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'completed', 'cancelled'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.flower_id' => ['nullable', 'required_without:items.*.bouquet_id', 'exists:flowers,id'],
            'items.*.bouquet_id' => ['nullable', 'required_without:items.*.flower_id', 'exists:bouquets,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
