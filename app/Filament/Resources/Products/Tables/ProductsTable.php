<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Services\PriceEngine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Category;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(
                fn ($query) => $query
                    ->orderBy(
                        Brand::query()
                            ->select('name')
                            ->whereColumn('brands.id', 'products.brand_id')
                    )
                    ->orderBy(
                        Category::query()
                            ->select('name')
                            ->whereColumn('categories.id', 'products.category_id')
                    )
                    ->orderBy('products.name')
            )

            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->size(56)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Product')
                    ->state(
                        fn (Product $record): string =>
                            self::formatProductTitle($record)
                    )
                    ->description(
                        fn (Product $record): string =>
                            self::formatProductDescription($record)
                    )
                    ->searchable(
                        query: function ($query, string $search) {
                            return $query->where(function ($query) use ($search) {
                                $query
                                    ->where('products.name', 'like', "%{$search}%")
                                    ->orWhere('products.code', 'like', "%{$search}%")
                                    ->orWhereHas(
                                        'brand',
                                        fn ($brandQuery) =>
                                            $brandQuery->where(
                                                'name',
                                                'like',
                                                "%{$search}%"
                                            )
                                    );
                            });
                        }
                    )
                    ->sortable()
                    ->wrap()
                    ->grow()
                    ->toggleable(),

                TextColumn::make('offer_display')
                    ->label('Special Offer')
                    ->badge()
                    ->state(
                        fn (Product $record): string =>
                            self::formatOffer($record)
                    )
                    ->color(
                        fn (string $state): string =>
                            self::offerColor($state)
                    )
                    ->toggleable(),

                TextColumn::make('base_price')
                    ->label('Base Price')
                    ->money('GBP')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('current_price')
                    ->label('Current Price')
                    ->state(
                        fn (Product $record): float =>
                            PriceEngine::selling($record)
                    )
                    ->money('GBP')
                    ->color(
                        fn (Product $record): string =>
                            PriceEngine::offerIsActive($record)
                                ? 'success'
                                : 'gray'
                    )
                    ->weight(
                        fn (Product $record): string =>
                            PriceEngine::offerIsActive($record)
                                ? 'bold'
                                : 'normal'
                    )
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('stock_display')
                    ->label('Stock')
                    ->state(
                        fn (Product $record): string =>
                            self::formatStock($record)
                    )
                    ->tooltip(
                        fn (Product $record): string =>
                            self::formatStockTooltip($record)
                    )
                    ->badge()
                    ->color(
                        fn (Product $record): string =>
                            self::stockColor($record)
                    )
                    ->sortable(
                        query: fn ($query, string $direction) =>
                            $query->orderBy(
                                'stock_quantity',
                                $direction
                            )
                    )
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(),
            ])

            ->reorderableColumns()
            ->deferColumnManager(false)

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    private static function formatProductTitle(
        Product $record
    ): string {
        $brand = trim(
            (string) ($record->brand?->name ?? '')
        );

        $name = trim(
            (string) ($record->name ?? '')
        );

        return trim("{$brand} {$name}");
    }

    private static function formatProductDescription(
        Product $record
    ): string {
        $code = trim(
            (string) ($record->code ?? '')
        );

        $sellingUnit = self::packUnitName($record);
        $packSize = self::formatPackSize($record);

        return "Code: {$code} • {$sellingUnit} | {$packSize}";
    }

    private static function formatPackSize(Product $record): string
    {
        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        $size = self::formatNumber(
            $record->size
        );

        $sizeUnit = self::shortSizeUnit(
            $record->sizeUnit?->name
        );

        if ($quantityPerPack <= 1) {
            return "{$size} {$sizeUnit}";
        }

        return "{$quantityPerPack} × {$size} {$sizeUnit}";
    }

    private static function formatPackSizeTooltip(
        Product $record
    ): string {
        $sellingUnit = self::packUnitName($record);

        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        return "{$sellingUnit} containing {$quantityPerPack} saleable units";
    }

    private static function formatStock(Product $record): string
    {
        if (self::isBulkKilogramProduct($record)) {
            return self::formatKilogramStock($record);
        }

        $stock = max(
            0,
            (int) round((float) ($record->stock_quantity ?? 0))
        );

        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        if ($stock === 0) {
            return 'Out of Stock';
        }

        if ($quantityPerPack <= 1) {
            return self::pluralize(
                $stock,
                'Unit',
                'Units'
            );
        }

        $fullPacks = intdiv(
            $stock,
            $quantityPerPack
        );

        $remainingUnits = $stock % $quantityPerPack;

        if ($fullPacks === 0) {
            return self::pluralize(
                $remainingUnits,
                'Unit',
                'Units'
            );
        }

        $packLabel = self::pluralizePackUnit(
            $fullPacks,
            self::packUnitName($record)
        );

        if ($remainingUnits === 0) {
            return $packLabel;
        }

        return $packLabel
            . ' + '
            . self::pluralize(
                $remainingUnits,
                'Unit',
                'Units'
            );
    }

    private static function formatStockTooltip(
        Product $record
    ): string {
        if (self::isBulkKilogramProduct($record)) {
            return number_format(
                max(0, (float) ($record->stock_quantity ?? 0)),
                3,
                '.',
                ''
            ) . ' kilograms in total';
        }

        $stock = max(
            0,
            (int) round((float) ($record->stock_quantity ?? 0))
        );

        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        if ($quantityPerPack <= 1) {
            return "{$stock} saleable units in total";
        }

        $packUnit = self::packUnitName($record);

        return "{$stock} saleable units in total"
            . " — {$quantityPerPack} units per {$packUnit}";
    }

    private static function stockColor(
        Product $record
    ): string {
        $stock = max(
            0,
            (float) ($record->stock_quantity ?? 0)
        );

        if ($stock <= 0) {
            return 'danger';
        }

        if (self::isBulkKilogramProduct($record)) {
            return $stock < 10
                ? 'warning'
                : 'success';
        }

        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        /*
         * Yellow when fewer than two full packs remain.
         * For single-unit products, fewer than 10 units is low stock.
         */
        $lowStockThreshold = $quantityPerPack > 1
            ? $quantityPerPack * 2
            : 10;

        if ($stock < $lowStockThreshold) {
            return 'warning';
        }

        return 'success';
    }

    private static function isBulkKilogramProduct(
        Product $record
    ): bool {
        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        $size = (float) ($record->size ?? 0);

        $sizeUnit = strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                Str::ascii(
                    trim((string) $record->sizeUnit?->name)
                )
            ) ?? ''
        );

        $isKilogram = in_array(
            $sizeUnit,
            [
                'KG',
                'KGS',
                'KILO',
                'KILOS',
                'KILOGRAM',
                'KILOGRAMS',
            ],
            true
        );

        return $quantityPerPack === 1
            && abs($size - 1.0) < 0.000001
            && $isKilogram;
    }

    private static function formatKilogramStock(
        Product $record
    ): string {
        $stock = round(
            max(0, (float) ($record->stock_quantity ?? 0)),
            3
        );

        if ($stock <= 0) {
            return 'Out of Stock';
        }

        $kilograms = (int) floor($stock);

        $grams = (int) round(
            ($stock - $kilograms) * 1000
        );

        /*
         * Protect against a floating-point edge case such as
         * 1.999999 becoming 1 kg + 1000 g.
         */
        if ($grams >= 1000) {
            $kilograms++;
            $grams = 0;
        }

        if ($grams === 0) {
            return self::pluralize(
                $kilograms,
                'kg',
                'kg'
            );
        }

        if ($kilograms === 0) {
            return self::pluralize(
                $grams,
                'g',
                'g'
            );
        }

        return self::pluralize(
            $kilograms,
            'kg',
            'kg'
        )
            . ' + '
            . self::pluralize(
                $grams,
                'g',
                'g'
            );
    }

    private static function packUnitName(
        Product $record
    ): string {
        $name = trim(
            (string) ($record->unit?->name ?? '')
        );

        return $name !== ''
            ? $name
            : 'Pack';
    }

    private static function pluralizePackUnit(
        int $quantity,
        string $unit
    ): string {
        $singular = self::singularizeUnit($unit);

        return $quantity
            . ' '
            . ($quantity === 1
                ? $singular
                : Str::plural($singular));
    }

    private static function singularizeUnit(
        string $unit
    ): string {
        $unit = trim($unit);

        if ($unit === '') {
            return 'Pack';
        }

        return Str::singular($unit);
    }

    private static function formatOffer(
        Product $record
    ): string {
        $percent = self::formatNumber(
            $record->special_offer_percent
        );

        if (
            ! $record->offer_active
            || (float) $record->special_offer_percent <= 0
        ) {
            return 'No Offer';
        }

        if (
            $record->offer_start_at
            && $record->offer_start_at->isFuture()
        ) {
            return "Scheduled {$percent}%";
        }

        if (
            $record->offer_end_at
            && $record->offer_end_at->isPast()
        ) {
            return "Expired {$percent}%";
        }

        return "Live {$percent}%";
    }

    private static function offerColor(
        string $state
    ): string {
        if (str_starts_with($state, 'Live')) {
            return 'success';
        }

        if (str_starts_with($state, 'Scheduled')) {
            return 'warning';
        }

        if (str_starts_with($state, 'Expired')) {
            return 'danger';
        }

        return 'gray';
    }

    private static function shortSizeUnit(
        ?string $unit
    ): string {
        $normalized = strtoupper(
            trim((string) $unit)
        );

        return match ($normalized) {
            'GRAM', 'GRAMS', 'G', 'GR' => 'g',
            'KILOGRAM', 'KILOGRAMS', 'KG' => 'kg',
            'MILLILITRE', 'MILLILITRES',
            'MILLILITER', 'MILLILITERS',
            'ML' => 'ml',
            'LITRE', 'LITRES',
            'LITER', 'LITERS',
            'L' => 'l',
            'PIECE', 'PIECES',
            'UNIT', 'UNITS',
            'PC', 'PCS' => 'pc',
            default => strtolower(
                trim((string) $unit)
            ),
        };
    }

    private static function formatNumber(
        mixed $value
    ): string {
        if ($value === null || $value === '') {
            return '—';
        }

        return rtrim(
            rtrim(
                number_format(
                    (float) $value,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }

    private static function pluralize(
        int $quantity,
        string $singular,
        string $plural
    ): string {
        return $quantity
            . ' '
            . ($quantity === 1
                ? $singular
                : $plural);
    }
}