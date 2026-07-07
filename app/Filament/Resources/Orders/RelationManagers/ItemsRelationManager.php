<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('product_id')
                    ->label('Product')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query
                            ->where('is_active', true)
                            ->orderBy('name')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('quantity')
                    ->numeric()
                    ->default(1)
                    ->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('quantity'),

                TextColumn::make('unit_price')
                    ->money('GBP'),

                TextColumn::make('line_total')
                    ->money('GBP'),

            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}