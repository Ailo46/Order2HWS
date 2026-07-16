<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PriceLevel;
use App\Models\Setting;
use Carbon\Carbon;

class PriceEngine
{
    /**
     * Product Base Price
     */
    public static function base(Product $product): float
    {
        return (float) $product->base_price;
    }

    /**
     * Product Offer Price
     */
    public static function offer(Product $product): float
    {
        if (! self::offerIsActive($product)) {
            return self::base($product);
        }

        return round(
            self::base($product)
            * (100 - $product->special_offer_percent)
            / 100,
            2
        );
    }

    /**
     * Current Selling Price
     */
    public static function selling(Product $product): float
    {
        return self::offer($product);
    }

    /**
     * Customer Price
     */
    public static function customerPrice(
        Product $product,
        ?PriceLevel $priceLevel
    ): float {

        $price = self::selling($product);

        if (! $priceLevel) {
            return $price;
        }

        $markup = match ($priceLevel->name) {

            'Delivery Price'
                => Setting::getDecimal('delivery_markup_percent'),

            'Cash & Carry'
                => Setting::getDecimal('cash_and_carry_markup_percent'),

            'End User'
                => Setting::getDecimal('end_user_markup_percent'),

            default => 0,

        };

        return round(
            $price * (100 + $markup) / 100,
            2
        );
    }

    /**
     * Price Preview
     */
    public static function preview(Product $product): array
    {
        return [

            'base' => self::base($product),

            'offer' => self::offer($product),

            'cash_and_carry' => round(
                self::selling($product)
                * (100 + Setting::getDecimal('cash_and_carry_markup_percent'))
                / 100,
                2
            ),

            'delivery' => round(
                self::selling($product)
                * (100 + Setting::getDecimal('delivery_markup_percent'))
                / 100,
                2
            ),

            'consumer' => round(
                self::selling($product)
                * (100 + Setting::getDecimal('end_user_markup_percent'))
                / 100,
                2
            ),

        ];
    }

    /**
     * Offer Active?
     */
    public static function offerIsActive(Product $product): bool
    {
        if (! $product->offer_active) {
            return false;
        }

        if ($product->special_offer_percent <= 0) {
            return false;
        }

        $now = Carbon::now();

        if (
            $product->offer_start_at &&
            $product->offer_start_at->gt($now)
        ) {
            return false;
        }

        if (
            $product->offer_end_at &&
            $product->offer_end_at->lt($now)
        ) {
            return false;
        }

        return true;
    }
}