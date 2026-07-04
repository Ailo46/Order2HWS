<?php

namespace App\Filament\Resources\SizeUnits\Pages;

use App\Filament\Resources\SizeUnits\SizeUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSizeUnits extends ListRecords
{
    protected static string $resource = SizeUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
