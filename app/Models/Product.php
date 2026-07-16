<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        'code',
        'barcode',
        'sku',

        /*
        |--------------------------------------------------------------------------
        | Classification
        |--------------------------------------------------------------------------
        */

        'brand_id',
        'category_id',

        /*
        |--------------------------------------------------------------------------
        | Details
        |--------------------------------------------------------------------------
        */

        'name',
        'short_description',

        /*
        |--------------------------------------------------------------------------
        | Packaging
        |--------------------------------------------------------------------------
        */

        'unit_id',
        'qty_per_pack',
        'size',
        'size_unit_id',

        'can_be_sold_as_unit',

        /*
        |--------------------------------------------------------------------------
        | Pricing
        |--------------------------------------------------------------------------
        */

        'base_price',

        'special_offer_percent',

        'offer_active',
        'offer_start_at',
        'offer_end_at',

        'vat_percent',

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        'stock_quantity',

        /*
        |--------------------------------------------------------------------------
        | Media
        |--------------------------------------------------------------------------
        */

        'image',

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'is_active',
    ];

    protected $appends = [
        'selling_price',
    ];

    protected function casts(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            'base_price' => 'decimal:2',

            'special_offer_percent' => 'decimal:2',

            'offer_active' => 'boolean',

            'offer_start_at' => 'datetime',

            'offer_end_at' => 'datetime',

            'vat_percent' => 'decimal:2',

            /*
            |--------------------------------------------------------------------------
            | Packaging
            |--------------------------------------------------------------------------
            */

            'size' => 'decimal:2',

            'qty_per_pack' => 'integer',

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            'stock_quantity' => 'integer',

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'can_be_sold_as_unit' => 'boolean',

            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function sizeUnit(): BelongsTo
    {
        return $this->belongsTo(SizeUnit::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Price Accessors
    |--------------------------------------------------------------------------
    */

    public function getSellingPriceAttribute(): float
    {
        $price = (float) $this->base_price;

        $offerEnabled =
            $this->offer_active
            && $this->special_offer_percent > 0
            && (
                is_null($this->offer_start_at)
                || now()->greaterThanOrEqualTo($this->offer_start_at)
            )
            && (
                is_null($this->offer_end_at)
                || now()->lessThanOrEqualTo($this->offer_end_at)
            );

        if ($offerEnabled) {
            $price -= $price * ($this->special_offer_percent / 100);
        }

        return round($price, 2);
    }
}