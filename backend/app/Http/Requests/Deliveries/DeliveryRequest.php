<?php

namespace App\Http\Requests\Deliveries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id', Rule::unique('deliveries', 'order_id')->ignore($this->delivery)],
            'courier_id' => ['required', 'exists:users,id'],
            'delivery_date' => ['required', 'date'],
            'delivery_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', Rule::in(['pending', 'in_transit', 'delivered', 'failed'])],
            'recipient_signature' => ['nullable', 'string'],
        ];
    }
}
