<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flower extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'category_id',
        'supplier_id',
        'price',
        'stock_quantity',
        'season',
        'lifespan_days',
        'color',
        'image_url',
        'photo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'lifespan_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bouquetFlowers(): HasMany
    {
        return $this->hasMany(BouquetFlower::class);
    }
}
