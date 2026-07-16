<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PriceFactorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [

            [
                'key' => 'delivery_markup_percent',
                'value' => '5',
                'type' => 'decimal',
                'category' => 'pricing',
                'description' => 'Delivery Price Markup (%)',
            ],

            [
                'key' => 'cash_and_carry_markup_percent',
                'value' => '12',
                'type' => 'decimal',
                'category' => 'pricing',
                'description' => 'Cash & Carry Markup (%)',
            ],

            [
                'key' => 'end_user_markup_percent',
                'value' => '25',
                'type' => 'decimal',
                'category' => 'pricing',
                'description' => 'End User Markup (%)',
            ],

        ];

        foreach ($items as $item) {

            Setting::updateOrCreate(

                [
                    'key' => $item['key'],
                ],

                [
                    'value' => $item['value'],
                    'type' => $item['type'],
                    'category' => $item['category'],
                    'description' => $item['description'],
                ]

            );
        }
    }
}