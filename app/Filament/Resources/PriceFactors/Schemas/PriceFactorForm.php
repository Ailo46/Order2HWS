<?php

namespace App\Filament\Resources\PriceFactors\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PriceFactorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([

                Section::make('Price Factor')

                    ->schema([

                        TextInput::make('key')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('value')
                            ->label('Percentage')
                            ->numeric()
                            ->suffix('%')
                            ->required(),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                    ]),

            ]);
    }
}