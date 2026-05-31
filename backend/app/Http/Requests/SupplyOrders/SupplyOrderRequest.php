<?php

namespace App\Http\Requests\SupplyOrders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplyOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'ordered', 'received', 'cancelled'])],
        ];
    }
}
