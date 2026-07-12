<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customerType.name')
                    ->label('Type')
                    ->badge()
                    ->sortable()

                    ->formatStateUsing(function (?string $state): string {

                        return match ($state) {

                            'Consumer' => '👤 End Consumer',

                            'Cash & Carry Customer' => '🛒 C&C Customer',

                            'Managed Customer' => '🏪 Managed Shop',

                            default => $state ?? '',
                        };
                    }),

                TextColumn::make('priceLevel.name')
                    ->label('Price Level')
                    ->badge()
                    ->sortable(),

                TextColumn::make('default_discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d')
                    ->sortable(),

            ])

            ->filters([

                TrashedFilter::make(),

            ])

            ->recordActions([

                EditAction::make()

                    ->label(fn (Customer $record) =>

                        in_array(
                            $record->customerType?->name,
                            [
                                'Cash & Carry Customer',
                                'Consumer',
                            ],
                            true
                        )

                        ? 'Edit User'

                        : 'Edit Customer'
                    )

                    ->tooltip(fn (Customer $record) =>

                        in_array(
                            $record->customerType?->name,
                            [
                                'Cash & Carry Customer',
                                'Consumer',
                            ],
                            true
                        )

                        ? 'Open integrated User Account'

                        : 'Edit Customer Information'
                    )

                    ->icon(fn (Customer $record) =>

                        in_array(
                            $record->customerType?->name,
                            [
                                'Cash & Carry Customer',
                                'Consumer',
                            ],
                            true
                        )

                        ? Heroicon::OutlinedUser

                        : Heroicon::OutlinedBuildingStorefront
                    ),

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