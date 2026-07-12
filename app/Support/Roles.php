<?php

namespace App\Support;

use App\Models\CustomerType;
use App\Models\PriceLevel;

final class Roles
{
    /*
    |--------------------------------------------------------------------------
    | Group 1
    |--------------------------------------------------------------------------
    */

    public const ADMIN = 'Administrator';

    public const SALES_MANAGER = 'Sales Manager';

    public const SALES_AGENT = 'Sales Agent';

    /*
    |--------------------------------------------------------------------------
    | Group 2
    |--------------------------------------------------------------------------
    */

    public const WAREHOUSE_USER = 'Warehouse';

    /*
    |--------------------------------------------------------------------------
    | Group 3
    |--------------------------------------------------------------------------
    */

    public const LOGISTIC_USER = 'Logistics';

    /*
    |--------------------------------------------------------------------------
    | Group 4
    |--------------------------------------------------------------------------
    */

    public const CUSTOMER_CC = 'Customer C&C';

    /*
    |--------------------------------------------------------------------------
    | Group 5
    |--------------------------------------------------------------------------
    */

    public const END_CONSUMER = 'End Consumer';

    /*
    |--------------------------------------------------------------------------
    | Collections
    |--------------------------------------------------------------------------
    */

    public const SYSTEM_USERS = [

        self::ADMIN,

        self::SALES_MANAGER,

        self::SALES_AGENT,

        self::WAREHOUSE_USER,

        self::LOGISTIC_USER,

        self::CUSTOMER_CC,

        self::END_CONSUMER,

    ];

    public const INTERNAL_USERS = [

        self::ADMIN,

        self::SALES_MANAGER,

        self::SALES_AGENT,

        self::WAREHOUSE_USER,

        self::LOGISTIC_USER,

    ];

    public const CUSTOMER_USERS = [

        self::CUSTOMER_CC,

        self::END_CONSUMER,

    ];

    public const SALES_USERS = [

        self::SALES_MANAGER,

        self::SALES_AGENT,

    ];

    public const NON_PRICING_USERS = [

        self::WAREHOUSE_USER,

        self::LOGISTIC_USER,

    ];

    /*
    |--------------------------------------------------------------------------
    | Customer Mapping
    |--------------------------------------------------------------------------
    */

    public static function customerTypeId(string $role): int
    {
        return match ($role) {

            self::CUSTOMER_CC =>
                CustomerType::where('name', 'Cash & Carry Customer')->value('id'),

            self::END_CONSUMER =>
                CustomerType::where('name', 'Consumer')->value('id'),

            default =>
                CustomerType::where('name', 'Managed Customer')->value('id'),
        };
    }

    public static function priceLevelId(string $role): int
    {
        return match ($role) {

            self::CUSTOMER_CC =>
                PriceLevel::where('name', 'Cash & Carry')->value('id'),

            self::END_CONSUMER =>
                PriceLevel::where('name', 'End User')->value('id'),

            default =>
                PriceLevel::where('name', 'Delivery Price')->value('id'),
        };
    }
}