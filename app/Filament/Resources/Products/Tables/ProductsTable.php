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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')

            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(44),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('pack_size')
                    ->label('Pack / Size')
                    ->state(
                        fn (Product $record): string =>
                            self::formatPackSize($record)
                    )
                    ->tooltip(
                        fn (Product $record): string =>
                            self::formatPackSizeTooltip($record)
                    ),

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
                    ),

                TextColumn::make('base_price')
                    ->label('Base Price')
                    ->money('GBP')
                    ->sortable()
                    ->color('gray'),

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
                    ),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

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
                    ),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])

            ->filters([
                TrashedFilter::make(),
            ])

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
        $sellingUnit = $record->unit?->name ?? 'Pack';

        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        return "{$sellingUnit} containing {$quantityPerPack} saleable units";
    }

    private static function formatStock(Product $record): string
    {
        $stock = max(
            0,
            (int) ($record->stock_quantity ?? 0)
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

        $packLabel = self::pluralize(
            $fullPacks,
            'Pack',
            'Packs'
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
        $stock = max(
            0,
            (int) ($record->stock_quantity ?? 0)
        );

        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        return "{$stock} saleable units in total"
            . ($quantityPerPack > 1
                ? " — {$quantityPerPack} units per pack"
                : '');
    }

    private static function stockColor(
        Product $record
    ): string {
        $stock = max(
            0,
            (int) ($record->stock_quantity ?? 0)
        );

        $quantityPerPack = max(
            1,
            (int) ($record->qty_per_pack ?? 1)
        );

        if ($stock <= 0) {
            return 'danger';
        }

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