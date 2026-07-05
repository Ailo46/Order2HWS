<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Customer')
                    ->components([

                        Grid::make(2)
                            ->components([

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                            ]),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),

                    ]),

                Section::make('Order Information')
                    ->components([

                        Grid::make(3)
                            ->components([

                                DatePicker::make('order_date')
                                    ->label('Order Date')
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('requested_delivery_date')
                                    ->label('Requested Delivery Date'),

                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'confirmed' => 'Confirmed',
                                    ])
                                    ->default('draft')
                                    ->required(),

                            ]),

                    ]),

            ]);
    }
}