<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Support\Roles;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->defaultSort('id', 'desc')

            ->columns([

                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agent_name')
                    ->label('Sales Agent')
                    ->searchable()
                    ->toggleable()
                    ->visible(fn () =>
                        auth()->user()?->hasAnyRole([
                            Roles::ADMIN,
                            Roles::SALES_MANAGER,
                        ])
                    ),

                TextColumn::make('agent_code')
                    ->label('Code')
                    ->badge()
                    ->toggleable()
                    ->visible(fn () =>
                        auth()->user()?->hasAnyRole([
                            Roles::ADMIN,
                            Roles::SALES_MANAGER,
                        ])
                    ),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('order_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->money('GBP')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->since(),

            ])

            ->filters([
                //
            ])

            ->recordActions([

                EditAction::make()
                    ->visible(function (Order $record): bool {

                        $user = auth()->user();

                        if (! $user) {
                            return false;
                        }

                        if ($user->hasAnyRole([
                            Roles::ADMIN,
                            Roles::SALES_MANAGER,
                        ])) {
                            return true;
                        }

                        if ($user->hasRole(Roles::SALES_AGENT)) {

                            return $record->created_by === $user->id
                                && $record->status === 'draft';
                        }

                        return false;
                    }),

            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make()
                        ->visible(fn () =>
                            auth()->user()?->hasAnyRole([
                                Roles::ADMIN,
                                Roles::SALES_MANAGER,
                            ])
                        ),

                ]),

            ]);
    }
}