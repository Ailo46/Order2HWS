<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Hash;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

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
                            ->helperText('Two-digit code'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                    ])
                    ->columns(3),

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

                Section::make('Security')
                    ->schema([

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn(string $operation) => $operation === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state)),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->required(fn(string $operation) => $operation === 'create')
                            ->same('password')
                            ->dehydrated(false),

                    ])
                    ->columns(2),

                Section::make('Authorization')
                    ->schema([

                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->searchable()
                            ->preload()
                            ->live(),

                        TextInput::make('max_discount_percent')
                            ->label('Maximum Discount %')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('Maximum discount allowed for Sales Agent'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                    ])
                    ->columns(3),

            ]);
    }
}