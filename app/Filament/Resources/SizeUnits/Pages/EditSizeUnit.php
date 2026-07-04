<?php

namespace App\Filament\Resources\SizeUnits\Pages;

use App\Filament\Resources\SizeUnits\SizeUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSizeUnit extends EditRecord
{
    protected static string $resource = SizeUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
