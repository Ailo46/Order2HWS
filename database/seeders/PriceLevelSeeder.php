<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceLevel;

class PriceLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'code' => 'CASH',
                'name' => 'Cash & Carry',
                'price_adjustment_percent' => 0,
            ],
            [
                'code' => 'DELIVERY',
                'name' => 'Delivery Price',
                'price_adjustment_percent' => 0,
            ],
            [
                'code' => 'ENDUSER',
                'name' => 'End User',
                'price_adjustment_percent' => 0,
            ],
        ];

        foreach ($levels as $level) {
            PriceLevel::updateOrCreate(
                ['code' => $level['code']],
                $level
            );
        }
    }
}