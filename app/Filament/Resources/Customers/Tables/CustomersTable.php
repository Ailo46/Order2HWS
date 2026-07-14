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

                TextColumn::make('display_code')
                    ->label('Code')
                    ->state(function (Customer $record): string {

                        return match ($record->customerType?->name) {

                            'Consumer' => 'N/A',

                            default => filled($record->code)
                                ? $record->code
                                : 'N/A',
                        };
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('display_name')
                    ->label('Customer')
                    ->state(function (Customer $record): string {

                        return match ($record->customerType?->name) {

                            'Consumer' =>
                                $record->contact_name ?: '(No Name)',

                            'Cash & Carry Customer' =>
                                filled($record->name)
                                    ? $record->name
                                    : ($record->contact_name ?: '(No Name)'),

                            default =>
                                $record->name ?? '(No Name)',
                        };
                    })
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

                TextColumn::make('display_sales_agent')
                    ->label('Sales Agent')

                    ->state(function (Customer $record): string {

                        return match ($record->customerType?->name) {

                            'Consumer',
                            'Cash & Carry Customer'
                                => 'Direct',

                            'Managed Customer'
                                => filled($record->salesAgent?->name)
                                    ? $record->salesAgent->name
                                    : 'Not Yet',

                            default => '',
                        };
                    })

                    ->badge()

                    ->color(function (Customer $record): string {

                        return match ($record->customerType?->name) {

                            'Consumer',
                            'Cash & Carry Customer'
                                => 'gray',

                            'Managed Customer'
                                => filled($record->salesAgent?->name)
                                    ? 'success'
                                    : 'danger',

                            default => 'gray',
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