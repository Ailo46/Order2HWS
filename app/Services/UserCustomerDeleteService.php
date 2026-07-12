<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;

class UserCustomerDeleteService
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user) {

            if (! $user->hasAnyRole(Roles::CUSTOMER_USERS)) {
                return;
            }

            Customer::where('email', $user->email)->delete();
        });
    }
}