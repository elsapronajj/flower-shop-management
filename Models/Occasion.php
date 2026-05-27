<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Occasion extends Model
{
    protected $fillable = ['name', 'description', 'event_date', 'discount_percentage'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'discount_percentage' => 'decimal:2',
        ];
    }
}
