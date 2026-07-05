<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Identity
        'code',
        'name',
        'is_active',

        // Business Classification
        'customer_type_id',
        'price_level_id',

        // Pricing
        'default_discount_percent',

        // Contact Information
        'contact_name',
        'phone',
        'mobile',
        'email',

        // Address
        'address',

        // Notes
        'notes',
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}