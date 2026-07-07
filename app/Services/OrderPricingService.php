<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;

class OrderPricingService
{
    public function calculate(
        Product $product,
        Customer $customer,
        float $quantity,
        float $agentDiscountPercent = 0,
    ): array {

        /*
        |--------------------------------------------------------------------------
        | 1) Product Base Price
        |--------------------------------------------------------------------------
        */

        $basePrice = (float) $product->base_price;

        /*
        |--------------------------------------------------------------------------
        | 2) Price Level Adjustment
        |--------------------------------------------------------------------------
        */

        $priceLevelPercent =
            (float) optional($customer->priceLevel)
                ->price_adjustment_percent;

        $priceAfterLevel = round(
            $basePrice * (1 + ($priceLevelPercent / 100)),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | 3) Customer Default Discount
        | این دیگر تخفیف محسوب نمی‌شود.
        | این قیمت پایه مشتری است.
        |--------------------------------------------------------------------------
        */

        $customerDiscount =
            (float) $customer->default_discount_percent;

        $customerUnitPrice = round(
            $priceAfterLevel * (1 - ($customerDiscount / 100)),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | 4) Sales Agent Discount
        |--------------------------------------------------------------------------
        */

        $netUnitPrice = round(
            $customerUnitPrice * (1 - ($agentDiscountPercent / 100)),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | 5) VAT
        |--------------------------------------------------------------------------
        */

        $vatPercent = (float) $product->vat_percent;

        $lineNet = round(
            $netUnitPrice * $quantity,
            2
        );

        $vatAmount = round(
            $lineNet * ($vatPercent / 100),
            2
        );

        $lineTotal = round(
            $lineNet + $vatAmount,
            2
        );

        return [

            'product_snapshot' => $this->snapshot($product),

            // قیمت مشتری
            'unit_price' => $customerUnitPrice,

            // فقط تخفیف عامل فروش
            'discount_percent' => $agentDiscountPercent,

            // مبلغ واقعی تخفیف عامل
            'discount_amount' => round(
                ($customerUnitPrice - $netUnitPrice) * $quantity,
                2
            ),

            // قیمت واحد بعد از تخفیف عامل
            'net_unit_price' => $netUnitPrice,

            'vat_percent' => $vatPercent,

            'vat_amount' => $vatAmount,

            'line_total' => $lineTotal,
        ];
    }

    public function snapshot(Product $product): array
    {
        return [

            'id' => $product->id,

            'code' => $product->code,

            'name' => $product->name,

            'brand' => optional($product->brand)->name,

            'category' => optional($product->category)->name,

            'unit' => optional($product->unit)->name,

            'qty_per_pack' => $product->qty_per_pack,

            'size' => $product->size,

            'size_unit' => optional($product->sizeUnit)->name,

            'can_be_sold_as_unit'
                => $product->can_be_sold_as_unit,

            'base_price' => $product->base_price,

            'vat_percent' => $product->vat_percent,
        ];
    }
}