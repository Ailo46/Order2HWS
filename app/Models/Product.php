<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [

        // Identity
        'code',
        'sku',
        'barcode',

        // Classification
        'category_id',
        'brand_id',

        // Product
        'name',
        'short_description',

        // Packaging
        'unit_id',
        'qty_per_pack',
        'size',
        'size_unit_id',

        // Selling
        'can_sell_unit',

        // Pricing
        'base_price',
        'vat_percent',
        'special_offer_percent',

        // Inventory
        'stock_quantity',
        'minimum_stock',

        // Visibility
        'visible_consumer',
        'visible_cash_customer',
        'visible_sales_agent',

        // Status
        'expiry_date',
        'is_active',

        // Media
        'image',
    ];

    protected function casts(): array
    {
        return [

            'size' => 'decimal:2',

            'base_price' => 'decimal:2',

            'vat_percent' => 'decimal:2',

            'special_offer_percent' => 'decimal:2',

            'can_sell_unit' => 'boolean',

            'visible_consumer' => 'boolean',

            'visible_cash_customer' => 'boolean',

            'visible_sales_agent' => 'boolean',

            'is_active' => 'boolean',

            'expiry_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function sizeUnit(): BelongsTo
    {
        return $this->belongsTo(SizeUnit::class);
    }
}