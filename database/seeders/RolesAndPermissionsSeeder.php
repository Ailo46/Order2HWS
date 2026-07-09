<?php

namespace Database\Seeders;

use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [

            Roles::ADMIN,

            Roles::SALES_MANAGER,

            Roles::SALES_AGENT,

            Roles::DISTRIBUTION_AGENT,

            Roles::WAREHOUSE_SELLER,

            Roles::RETAIL_CUSTOMER,

            Roles::CONSUMER,

        ];

        foreach ($roles as $role) {

            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);

        }
    }
}