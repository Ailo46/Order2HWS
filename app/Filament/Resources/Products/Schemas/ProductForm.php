<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
use App\Models\SizeUnit;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([

                Section::make('Identity')

                    ->schema([

                        Grid::make(4)

                            ->schema([

                                TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                TextInput::make('barcode'),

                                TextInput::make('sku'),

                                Toggle::make('is_active')
                                    ->default(true),

                            ]),

                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull(),

                    ]),

                Section::make('Classification')

                    ->schema([

                        Grid::make(2)

                            ->schema([

                                Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                            ]),

                    ]),

                Section::make('Packaging')

                    ->schema([

                        Grid::make(4)

                            ->schema([

                                Select::make('unit_id')
                                    ->relationship('unit', 'name')
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
                                    ->preload()
                                    ->required(),

                            ]),

                        Toggle::make('can_be_sold_as_unit')
                            ->label('Can be sold as unit'),

                    ]),

                Section::make('Pricing')

                    ->schema([

                        Grid::make(3)

                            ->schema([

                                TextInput::make('base_price')
                                    ->numeric()
                                    ->prefix('£')
                                    ->required(),

                                TextInput::make('special_offer_percent')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0),

                                TextInput::make('vat_percent')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0),

                            ]),

                    ]),

                Section::make('Inventory')

                    ->schema([

                        TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0)
                            ->required(),

                    ]),

                Section::make('Media')

                    ->schema([

                        FileUpload::make('image')
                            ->image()
                            ->directory('products'),

                    ]),

                Section::make('Description')

                    ->schema([

                        Textarea::make('short_description')
                            ->rows(4)
                            ->columnSpanFull(),

                    ]),
                
                Section::make('Visibility')
                    ->schema([
                        //
                    ])
                    ->collapsed(),

            ]);
    }
}