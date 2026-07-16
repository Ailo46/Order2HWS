<?php

namespace App\Filament\Resources\PriceFactors\Pages;

use App\Filament\Resources\PriceFactors\PriceFactorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceFactor extends EditRecord
{
    protected static string $resource = PriceFactorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
