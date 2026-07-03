<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceLevel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'price_adjustment_percent',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}