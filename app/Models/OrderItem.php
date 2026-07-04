<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [

        'order_id',

        'product_id',

        'product_snapshot',

        'quantity',

        'unit_price',

        'discount_percent',

        'discount_amount',

        'vat_percent',

        'vat_amount',

        'line_total',

    ];

    protected function casts(): array
    {
        return [

            'product_snapshot' => 'array',

            'quantity' => 'decimal:2',

            'unit_price' => 'decimal:2',

            'discount_percent' => 'decimal:2',

            'discount_amount' => 'decimal:2',

            'vat_percent' => 'decimal:2',

            'vat_amount' => 'decimal:2',

            'line_total' => 'decimal:2',

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