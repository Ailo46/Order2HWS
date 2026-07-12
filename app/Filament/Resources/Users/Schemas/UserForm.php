<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Customer;
use App\Models\User;
use App\Support\Roles;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    protected static function selectedRoleName($get): ?string
    {
        $roleId = $get('roles');

        if (blank($roleId)) {
            return null;
        }

        return DB::table('roles')
            ->where('id', $roleId)
            ->value('name');
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Identity
                |--------------------------------------------------------------------------
                */

                Section::make('Identity')
                    ->schema([

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('agent_code')
                            ->label('Agent Code')
                            ->maxLength(2)
                            ->minLength(2)
                            ->unique(ignoreRecord: true)
                            ->helperText('Two-digit code')
                            ->visible(fn ($get) =>
                                self::selectedRoleName($get) === Roles::SALES_AGENT
                            ),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)

                            ->rule(function ($record) {

                                return function (string $attribute, $value, \Closure $fail) use ($record) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Ignore current User
                                    |--------------------------------------------------------------------------
                                    */

                                    $userExists = User::query()
                                        ->where('email', $value)
                                        ->when(
                                            $record,
                                            fn ($q) => $q->whereKeyNot($record->id)
                                        )
                                        ->exists();

                                    if ($userExists) {
                                        $fail('This email address is already in use.');
                                        return;
                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Ignore paired Customer of current User
                                    |--------------------------------------------------------------------------
                                    */

                                    $customerExists = Customer::query()
                                        ->whereNull('deleted_at')
                                        ->where('email', $value)
                                        ->when(
                                            $record,
                                            fn ($q) => $q->where('email', '!=', $record->email)
                                        )
                                        ->exists();

                                    if ($customerExists) {
                                        $fail('This email address is already in use.');
                                    }
                                };
                            }),

                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | Contact Information
                |--------------------------------------------------------------------------
                */

                Section::make('Contact Information')
                    ->schema([

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel(),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->tel(),

                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Authorization
                |--------------------------------------------------------------------------
                */

                Section::make('Authorization')
                    ->schema([

                        Select::make('roles')
                            ->label('Role')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->whereIn(
                                    'name',
                                    Roles::SYSTEM_USERS,
                                ),
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->columnSpan(2)
                            ->required(),

                        TextInput::make('max_discount_percent')
                            ->label('Maximum Discount %')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn ($get) =>
                                self::selectedRoleName($get) === Roles::SALES_AGENT
                            ),

                        TextInput::make('customer_discount_percent')
                            ->label('Customer Discount %')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->dehydrated(false)

                            ->afterStateHydrated(function ($component, $record) {

                                if (! $record) {
                                    return;
                                }

                                $customer = Customer::where('email', $record->email)->first();

                                $component->state(
                                    $customer?->default_discount_percent
                                );
                            })

                            ->visible(fn ($get) =>
                                in_array(
                                    self::selectedRoleName($get),
                                    [
                                        Roles::CUSTOMER_CC,
                                        Roles::END_CONSUMER,
                                    ],
                                    true
                                )
                            ),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                    ])
                    ->columns(4),

                /*
                |--------------------------------------------------------------------------
                | Security
                |--------------------------------------------------------------------------
                */

                Section::make('Security')
                    ->schema([

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->same('password')
                            ->dehydrated(false),

                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Customer Information
                |--------------------------------------------------------------------------
                */

                Section::make('Customer Information')
                    ->visible(fn ($get) => in_array(
                        self::selectedRoleName($get),
                        [
                            Roles::CUSTOMER_CC,
                            Roles::END_CONSUMER,
                        ],
                        true,
                    ))
                    ->schema([

                        TextInput::make('customer_code')
                            ->label('Customer Code')
                            ->maxLength(30)
                            ->dehydrated(false)

                            ->rule(function ($record) {

                                return function (string $attribute, $value, \Closure $fail) use ($record) {

                                    if (blank($value)) {
                                        return;
                                    }

                                    $query = Customer::query()
                                        ->whereNull('deleted_at')
                                        ->where('code', $value);

                                    if ($record) {

                                        $query->where('email', '!=', $record->email);
                                    }

                                    if ($query->exists()) {

                                        $fail('Customer Code already exists.');
                                    }
                                };
                            })

                            ->afterStateHydrated(function ($component, $record) {

                                if (! $record) {
                                    return;
                                }

                                $customer = Customer::where('email', $record->email)->first();

                                $component->state($customer?->code);
                            }),

                        TextInput::make('business_name')
                            ->label('Business Name')
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {

                                if (! $record) {
                                    return;
                                }

                                $customer = Customer::where('email', $record->email)->first();

                                $component->state($customer?->name);
                            }),

                        TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {

                                if (! $record) {
                                    return;
                                }

                                $customer = Customer::where('email', $record->email)->first();

                                $component->state($customer?->contact_name);
                            }),

                        TextInput::make('address')
                            ->label('Address')
                            ->columnSpanFull()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {

                                if (! $record) {
                                    return;
                                }

                                $customer = Customer::where('email', $record->email)->first();

                                $component->state($customer?->address);
                            }),

                    ])
                    ->columns(2),

            ]);
    }
}