<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [

        'order_id',
        'product_id',

        'product_code',
        'product_name',

        'brand_name',
        'category_name',

        'unit_name',
        'qty_per_pack',
        'size',
        'size_unit',

        'quantity',
        'sold_as_unit',

        'unit_price',
        'discount_percent',
        'vat_percent',

        'line_total',
    ];

    protected function casts(): array
    {
        return [

            'size'=>'decimal:2',

            'unit_price'=>'decimal:2',

            'discount_percent'=>'decimal:2',

            'vat_percent'=>'decimal:2',

            'line_total'=>'decimal:2',

            'sold_as_unit'=>'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}