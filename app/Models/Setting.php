<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [

        'key',
        'value',
        'type',
        'group',
        'description',

    ];

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    public static function getDecimal(string $key, float $default = 0): float
    {
        return (float) static::get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(

            ['key' => $key],

            ['value' => $value],

        );
    }
}