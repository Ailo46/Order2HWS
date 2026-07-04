<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SizeUnit;

class SizeUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [

            [
                'code' => 'G',
                'name' => 'Gram',
                'sort_order' => 10,
            ],

            [
                'code' => 'KG',
                'name' => 'Kilogram',
                'sort_order' => 20,
            ],

            [
                'code' => 'ML',
                'name' => 'Millilitre',
                'sort_order' => 30,
            ],

            [
                'code' => 'L',
                'name' => 'Litre',
                'sort_order' => 40,
            ],

            [
                'code' => 'PCS',
                'name' => 'Piece',
                'sort_order' => 50,
            ],

            [
                'code' => 'CM',
                'name' => 'Centimetre',
                'sort_order' => 60,
            ],

            [
                'code' => 'M',
                'name' => 'Metre',
                'sort_order' => 70,
            ],
        ];

        foreach ($units as $unit) {
            SizeUnit::updateOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}