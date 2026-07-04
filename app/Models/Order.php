<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'order_number',

        'customer_id',
        'sales_agent_id',

        'status',

        'subtotal',
        'discount_total',
        'vat_total',
        'grand_total',

        'customer_note',
        'internal_note',

        'submitted_at',
        'confirmed_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [

            'subtotal' => 'decimal:2',

            'discount_total' => 'decimal:2',

            'vat_total' => 'decimal:2',

            'grand_total' => 'decimal:2',

            'submitted_at' => 'datetime',

            'confirmed_at' => 'datetime',

            'delivered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesAgent(): BelongsTo
    {
        return $this->belongsTo(User::class,'sales_agent_id');
    }
}