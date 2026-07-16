<?php

namespace App\Filament\Resources\PriceFactors\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceFactorsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->defaultSort('key')

            ->columns([

                TextColumn::make('description')
                    ->label('Price Factor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Percentage')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->wrap(),

            ])

            ->filters([
                //
            ])

            ->recordActions([

                EditAction::make(),

            ])

            ->toolbarActions([
                //
            ]);
    }
}