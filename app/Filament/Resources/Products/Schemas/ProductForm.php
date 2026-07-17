<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SizeUnit;
use App\Models\Unit;
use App\Services\PriceEngine;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    protected static function pricePreview($get): array
    {
        $product = new Product();

        $product->forceFill([
            'base_price' => (float) ($get('base_price') ?? 0),

            'special_offer_percent' =>
                (float) ($get('special_offer_percent') ?? 0),

            'offer_active' => (bool) $get('offer_active'),

            'offer_start_at' => filled($get('offer_start_at'))
                ? $get('offer_start_at')
                : null,

            'offer_end_at' => filled($get('offer_end_at'))
                ? $get('offer_end_at')
                : null,
        ]);

        return PriceEngine::preview($product);
    }

    protected static function money(float $value): string
    {
        return '£' . number_format($value, 2);
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

                /*
                |--------------------------------------------------------------------------
                | Classification
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Packaging
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Pricing
                |--------------------------------------------------------------------------
                */

                Section::make('Pricing')

                    ->schema([

                        Grid::make(4)

                            ->schema([

                                TextInput::make('base_price')
                                    ->label('Base Price')
                                    ->numeric()
                                    ->prefix('£')
                                    ->required()
                                    ->live(onBlur: true),

                                TextInput::make('special_offer_percent')
                                    ->label('Special Offer')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->default(0)
                                    ->live(onBlur: true),

                                TextInput::make('vat_percent')
                                    ->label('VAT')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->default(0),

                                Toggle::make('offer_active')
                                    ->label('Offer Active')
                                    ->live()

                                    ->afterStateUpdated(function ($set, $state) {

                                        if (! $state) {

                                            $set('offer_start_at', null);

                                            $set('offer_end_at', null);
                                        }
                                    }),

                            ]),

                        Grid::make(2)

                            ->schema([

                                DateTimePicker::make('offer_start_at')
                                    ->label('Offer Starts')
                                    ->seconds(false)
                                    ->native(false)
                                    ->disabled(fn ($get) =>
                                        ! $get('offer_active')
                                    )
                                    ->required(fn ($get) =>
                                        $get('offer_active')
                                    )
                                    ->live(),

                                DateTimePicker::make('offer_end_at')
                                    ->label('Offer Ends')
                                    ->seconds(false)
                                    ->native(false)
                                    ->disabled(fn ($get) =>
                                        ! $get('offer_active')
                                    )
                                    ->required(fn ($get) =>
                                        $get('offer_active')
                                    )
                                    ->after('offer_start_at')
                                    ->live(),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Live Price Preview
                |--------------------------------------------------------------------------
                */

                Section::make('Live Price Preview')

                    ->description(
                        'Calculated from the current form values and active Price Factors.'
                    )

                    ->schema([

                        Grid::make(5)

                            ->schema([

                                Placeholder::make('preview_base_price')
                                    ->label('Base Price')
                                    ->content(fn ($get): string =>
                                        self::money(
                                            self::pricePreview($get)['base']
                                        )
                                    ),

                                Placeholder::make('preview_offer_price')
                                    ->label('Offer Price')
                                    ->content(fn ($get): string =>
                                        self::money(
                                            self::pricePreview($get)['offer']
                                        )
                                    ),

                                Placeholder::make('preview_delivery')
                                    ->label('Delivery')
                                    ->content(fn ($get): string =>
                                        self::money(
                                            self::pricePreview($get)['delivery']
                                        )
                                    ),

                                Placeholder::make('preview_cash_and_carry')
                                    ->label('C&C')
                                    ->content(fn ($get): string =>
                                        self::money(
                                            self::pricePreview($get)['cash_and_carry']
                                        )
                                    ),

                                Placeholder::make('preview_consumer')
                                    ->label('Consumer')
                                    ->content(fn ($get): string =>
                                        self::money(
                                            self::pricePreview($get)['consumer']
                                        )
                                    ),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Inventory
                |--------------------------------------------------------------------------
                */

                Section::make('Inventory')

                    ->schema([

                        TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0)
                            ->required(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Media
                |--------------------------------------------------------------------------
                */

                Section::make('Media')

                    ->schema([

                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Description
                |--------------------------------------------------------------------------
                */

                Section::make('Description')

                    ->schema([

                        Textarea::make('short_description')
                            ->rows(4)
                            ->columnSpanFull(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Visibility
                |--------------------------------------------------------------------------
                */

                Section::make('Visibility')
                    ->schema([
                        //
                    ])
                    ->collapsed(),

            ]);
    }
}