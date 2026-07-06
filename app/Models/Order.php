<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'created_by',
        'agent_name',
        'agent_code',
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

            // تاریخ سفارش
            $order->order_date ??= now()->toDateString();

            // وضعیت پیشفرض
            $order->status ??= 'draft';

            // مقادیر مالی اولیه
            $order->subtotal ??= 0;
            $order->discount_total ??= 0;
            $order->vat_total ??= 0;
            $order->grand_total ??= 0;

            // ثبت کننده سفارش
            if (auth()->check()) {

                $order->created_by = auth()->id();

                $user = auth()->user();

                if ($user->hasRole(\App\Support\Roles::SALES_AGENT)) {

                    $order->agent_name = $user->name;
                    $order->agent_code = $user->agent_code;
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}