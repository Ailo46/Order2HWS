<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Identity')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20),

                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100),

                            ]),

                    ]),

                Section::make('Details')
                    ->schema([

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->default(true),

                    ]),

            ]);
    }
}