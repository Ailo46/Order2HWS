<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\PriceLevel;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserCustomerService
{
    public function handle(User $user, array $data = []): void
    {
        DB::transaction(function () use ($user, $data) {

            /*
            |--------------------------------------------------------------------------
            | Only Customer Users own Customer record
            |--------------------------------------------------------------------------
            */

            if (! $user->hasAnyRole(Roles::CUSTOMER_USERS)) {

                Customer::where('email', $user->email)->delete();

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Customer Type / Price Level
            |--------------------------------------------------------------------------
            */

            $customerTypeId = null;
            $priceLevelId = null;

            if ($user->hasRole(Roles::CUSTOMER_CC)) {

                $customerTypeId = CustomerType::where(
                    'name',
                    'Cash & Carry Customer'
                )->value('id');

                $priceLevelId = PriceLevel::where(
                    'name',
                    'Cash & Carry'
                )->value('id');

            } elseif ($user->hasRole(Roles::END_CONSUMER)) {

                $customerTypeId = CustomerType::where(
                    'name',
                    'Consumer'
                )->value('id');

                $priceLevelId = PriceLevel::where(
                    'name',
                    'End User'
                )->value('id');
            }

            /*
            |--------------------------------------------------------------------------
            | Existing Customer
            |--------------------------------------------------------------------------
            */

            $customer = Customer::withTrashed()
                ->where('email', $user->email)
                ->first();

            if ($customer?->trashed()) {
                $customer->restore();
            }

            if (! $customer) {

                $customer = new Customer();

                $customer->code = $data['customer_code'] ?? null;
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Customer Code
            |--------------------------------------------------------------------------
            */

            $newCode = $data['customer_code'] ?? $customer->code;

            if (filled($newCode)) {

                $exists = Customer::withTrashed()
                    ->where('code', $newCode)
                    ->when(
                        $customer->exists,
                        fn ($q) => $q->whereKeyNot($customer->id)
                    )
                    ->exists();

                if ($exists) {

                    throw ValidationException::withMessages([
                        'customer_code' => 'Customer Code already exists.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Synchronize
            |--------------------------------------------------------------------------
            */

            $customer->code = $newCode;

            /*
            |--------------------------------------------------------------------------
            | Customer Name / Contact Name
            |--------------------------------------------------------------------------
            */

            if ($user->hasRole(Roles::END_CONSUMER)) {

                /*
                |--------------------------------------------------------------------------
                | End Consumer
                |--------------------------------------------------------------------------
                |
                | There is no Business Name.
                | Store Full Name into Customer Name.
                |
                */

                $customer->name = $user->name;

                $customer->contact_name = $user->name;

            } else {

                /*
                |--------------------------------------------------------------------------
                | Cash & Carry Customer
                |--------------------------------------------------------------------------
                */

                $customer->name = filled($data['business_name'] ?? null)
                    ? $data['business_name']
                    : $user->name;

                $customer->contact_name = filled($data['contact_name'] ?? null)
                    ? $data['contact_name']
                    : $user->name;
            }

            $customer->address =
                $data['address']
                ?? $customer->address;

            $customer->default_discount_percent =
                $data['customer_discount_percent']
                ?? $customer->default_discount_percent
                ?? 0;

            $customer->customer_type_id = $customerTypeId;

            $customer->price_level_id = $priceLevelId;

            $customer->sales_agent_id = null;

            $customer->phone = $user->phone;

            $customer->mobile = $user->mobile;

            $customer->email = $user->email;

            $customer->is_active = $user->is_active;

            if (blank($customer->notes)) {

                $customer->notes = 'Created automatically from User';
            }

            $customer->save();
        });
    }
}