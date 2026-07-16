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
                    ->square(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('pack_size')
                    ->label('Pack / Size')

                    ->state(function (Product $record): string {

                        $unit = $record->unit?->name ?? '—';

                        $quantity = $record->qty_per_pack ?? 1;

                        $size = filled($record->size)
                            ? rtrim(
                                rtrim(
                                    number_format(
                                        (float) $record->size,
                                        2,
                                        '.',
                                        ''
                                    ),
                                    '0'
                                ),
                                '.'
                            )
                            : '—';

                        $sizeUnit = $record->sizeUnit?->name ?? '';

                        return "{$unit} | {$quantity}x{$size}{$sizeUnit}";
                    }),

                TextColumn::make('offer_display')
                    ->label('Special Offer')
                    ->badge()

                    ->state(function (Product $record): string {

                        $percent = rtrim(
                            rtrim(
                                number_format(
                                    (float) $record->special_offer_percent,
                                    2,
                                    '.',
                                    ''
                                ),
                                '0'
                            ),
                            '.'
                        );

                        if (
                            ! $record->offer_active ||
                            (float) $record->special_offer_percent <= 0
                        ) {
                            return 'No Offer';
                        }

                        if (
                            $record->offer_start_at &&
                            $record->offer_start_at->isFuture()
                        ) {
                            return "Scheduled {$percent}%";
                        }

                        if (
                            $record->offer_end_at &&
                            $record->offer_end_at->isPast()
                        ) {
                            return "Expired {$percent}%";
                        }

                        return "Live {$percent}%";
                    })

                    ->color(function (string $state): string {

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
                    }),

                TextColumn::make('base_price')
                    ->label('Base Price')
                    ->money('GBP')
                    ->sortable(),

                TextColumn::make('current_price')
                    ->label('Current Price')

                    ->state(fn (Product $record): float =>
                        PriceEngine::selling($record)
                    )

                    ->money('GBP')

                    ->color(fn (Product $record): string =>
                        PriceEngine::offerIsActive($record)
                            ? 'success'
                            : 'gray'
                    )

                    ->weight(fn (Product $record): string =>
                        PriceEngine::offerIsActive($record)
                            ? 'bold'
                            : 'normal'
                    ),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()

                    ->color(function ($state): string {

                        $stock = (int) $state;

                        if ($stock <= 0) {
                            return 'danger';
                        }

                        if ($stock <= 10) {
                            return 'warning';
                        }

                        return 'success';
                    }),

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
}