<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Support\Roles;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->schema([
                        TextInput::make('code')
                            ->label('Customer Code')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Auto generated or enter manually')
                            ->helperText('Leave empty to let the system generate a code.'),

                        TextInput::make('name')
                            ->label('Customer Name')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(3),

                Section::make('Business Classification')
                    ->schema([
                        Select::make('customer_type_id')
                            ->label('Customer Type')
                            ->relationship(
                                name: 'customerType',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->orderBy('id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('price_level_id')
                            ->label('Price Level')
                            ->relationship('priceLevel', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('sales_agent_id')
                            ->label('Sales Agent')
                            ->relationship(
                                name: 'salesAgent',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) =>
                                    $query->role(Roles::SALES_AGENT)->orderBy('name')
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn () =>
                                auth()->user()->hasAnyRole([
                                    Roles::ADMIN,
                                    Roles::SALES_MANAGER,
                                ])
                            ),
                    ])
                    ->columns(3),
                
                Section::make('Pricing')
                    ->schema([
                        TextInput::make('default_discount_percent')
                            ->label('Default Discount (%)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ])
                    ->columns(1),
                
                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(30),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->tel()
                            ->maxLength(30),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Address Information')
                    ->schema([
                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}