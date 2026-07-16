<?php

namespace App\Filament\Resources\PriceFactors\Pages;

use App\Filament\Resources\PriceFactors\PriceFactorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceFactors extends ListRecords
{
    protected static string $resource = PriceFactorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
