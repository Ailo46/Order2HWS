<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Carbon\Carbon;
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
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('base_price')
                    ->label('Base Price')
                    ->money('GBP')
                    ->sortable(),

                TextColumn::make('special_offer_percent')
                    ->label('Offer')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('offer_price')
                    ->label('Offer Price')

                    ->state(function (Product $record) {

                        if (
                            ! $record->offer_active ||
                            $record->special_offer_percent <= 0
                        ) {
                            return null;
                        }

                        $price = $record->base_price *
                            (100 - $record->special_offer_percent) / 100;

                        return number_format($price, 2);
                    })

                    ->money('GBP')

                    ->color('success')

                    ->placeholder('-'),

                TextColumn::make('offer_status')

                    ->label('Offer Status')

                    ->badge()

                    ->state(function (Product $record) {

                        if (
                            ! $record->offer_active ||
                            $record->special_offer_percent <= 0
                        ) {
                            return 'No Offer';
                        }

                        $now = Carbon::now();

                        if (
                            $record->offer_start_at &&
                            $record->offer_start_at->isFuture()
                        ) {
                            return 'Scheduled';
                        }

                        if (
                            $record->offer_end_at &&
                            $record->offer_end_at->isPast()
                        ) {
                            return 'Expired';
                        }

                        return 'Live Offer';
                    })

                    ->color(function (string $state) {

                        return match ($state) {

                            'Live Offer' => 'success',

                            'Scheduled' => 'warning',

                            'Expired' => 'danger',

                            default => 'gray',
                        };
                    }),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable(),

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