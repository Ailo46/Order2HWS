<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'notes',

        'customer_type_id',
        'price_level_id',

        'default_discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_discount_percent' => 'decimal:2',
        ];
    }

    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function priceLevel(): BelongsTo
    {
        return $this->belongsTo(PriceLevel::class);
    }
}