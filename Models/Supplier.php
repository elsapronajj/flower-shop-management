<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact', 'phone', 'email', 'address', 'specialty'];

    public function flowers(): HasMany
    {
        return $this->hasMany(Flower::class);
    }

    public function supplyOrders(): HasMany
    {
        return $this->hasMany(SupplyOrder::class);
    }
}
