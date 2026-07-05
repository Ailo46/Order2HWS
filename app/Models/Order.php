<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'order_date',
        'requested_delivery_date',
        'status',
        'subtotal',
        'discount_total',
        'vat_total',
        'grand_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'requested_delivery_date' => 'date',

            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {

            // تاریخ سفارش اگر خالی بود امروز
            $order->order_date ??= now()->toDateString();

            // وضعیت پیشفرض
            $order->status ??= 'draft';

            // مقادیر مالی اولیه
            $order->subtotal ??= 0;
            $order->discount_total ??= 0;
            $order->vat_total ??= 0;
            $order->grand_total ??= 0;
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}