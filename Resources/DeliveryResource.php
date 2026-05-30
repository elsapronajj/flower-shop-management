<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'courier_id' => $this->courier_id,
            'delivery_date' => $this->delivery_date?->toDateString(),
            'delivery_time' => $this->delivery_time,
            'status' => $this->status,
            'recipient_signature' => $this->recipient_signature,
            'order' => new OrderResource($this->whenLoaded('order')),
            'courier' => new UserResource($this->whenLoaded('courier')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
