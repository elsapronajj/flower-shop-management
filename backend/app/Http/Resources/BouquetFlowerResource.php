<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BouquetFlowerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bouquet_id' => $this->bouquet_id,
            'flower_id' => $this->flower_id,
            'quantity' => $this->quantity,
            'bouquet' => new BouquetResource($this->whenLoaded('bouquet')),
            'flower' => new FlowerResource($this->whenLoaded('flower')),
        ];
    }
}
