<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerType;

class CustomerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'CASH',
                'name' => 'Cash & Carry Customer',
            ],
            [
                'code' => 'MANAGED',
                'name' => 'Managed Customer',
            ],
            [
                'code' => 'CONSUMER',
                'name' => 'Consumer',
            ],
        ];

        foreach ($types as $type) {
            CustomerType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}