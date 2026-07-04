<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Identity')
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                TextInput::make('code')
                                    ->label('Product Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),

                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100),

                                TextInput::make('barcode')
                                    ->label('Barcode')
                                    ->maxLength(100),

                            ]),

                    ]),

                Section::make('Classification')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                            ]),

                    ]),

                Section::make('Product')
                    ->schema([

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('short_description')
                            ->rows(3),

                    ]),

                Section::make('Packaging')
                    ->schema([

                        Grid::make(4)
                            ->schema([

                                Select::make('unit_id')
                                    ->relationship('unit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('qty_per_pack')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                TextInput::make('size')
                                    ->numeric()
                                    ->required(),

                                Select::make('size_unit_id')
                                    ->relationship('sizeUnit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                            ]),

                    ]),

                Section::make('Pricing')
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                TextInput::make('base_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('£'),

                                TextInput::make('vat_percent')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%'),

                                TextInput::make('special_offer_percent')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%'),

                            ]),

                    ]),

                Section::make('Inventory')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextInput::make('stock_quantity')
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('minimum_stock')
                                    ->numeric()
                                    ->default(0),

                            ]),

                    ]),

                Section::make('Availability')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                Toggle::make('can_sell_unit')
                                    ->label('Can sell by unit'),

                                Toggle::make('is_active')
                                    ->default(true),

                            ]),

                        Grid::make(3)
                            ->schema([

                                Toggle::make('visible_consumer')
                                    ->default(true),

                                Toggle::make('visible_cash_customer')
                                    ->default(true),

                                Toggle::make('visible_sales_agent')
                                    ->default(true),

                            ]),

                        DatePicker::make('expiry_date'),

                    ]),

                Section::make('Image')
                    ->schema([

                        FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->imageEditor(),

                    ]),
            ]);
    }
}