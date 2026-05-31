<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'order_date' => $this->order_date?->toISOString(),
            'delivery_date' => $this->delivery_date?->toDateString(),
            'delivery_address' => $this->delivery_address,
            'card_message' => $this->card_message,
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'delivery' => new DeliveryResource($this->whenLoaded('delivery')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
