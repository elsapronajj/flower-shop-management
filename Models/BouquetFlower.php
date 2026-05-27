<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BouquetFlower extends Model
{
    protected $fillable = ['bouquet_id', 'flower_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function bouquet(): BelongsTo
    {
        return $this->belongsTo(Bouquet::class);
    }

    public function flower(): BelongsTo
    {
        return $this->belongsTo(Flower::class);
    }
}
