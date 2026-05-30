<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'bouquet_id' => $this->bouquet_id,
            'flower_id' => $this->flower_id,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
            'amount' => (float) ($this->amount ?? $this->subtotal),
            'bouquet' => new BouquetResource($this->whenLoaded('bouquet')),
            'flower' => new FlowerResource($this->whenLoaded('flower')),
        ];
    }
}
