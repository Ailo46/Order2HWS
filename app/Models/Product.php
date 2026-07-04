<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'code',
        'barcode',
        'sku',

        'brand_id',
        'category_id',

        'name',
        'short_description',

        'unit_id',
        'qty_per_pack',
        'size',
        'size_unit_id',

        'can_be_sold_as_unit',

        'base_price',
        'special_offer_percent',
        'vat_percent',

        'stock_quantity',

        'image',

        'is_active',
    ];

    protected function casts(): array
    {
        return [

            'base_price' => 'decimal:2',

            'special_offer_percent' => 'decimal:2',

            'vat_percent' => 'decimal:2',

            'size' => 'decimal:2',

            'qty_per_pack' => 'integer',

            'stock_quantity' => 'integer',

            'can_be_sold_as_unit' => 'boolean',

            'is_active' => 'boolean',
        ];
    }

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
}