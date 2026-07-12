<?php

namespace App\Filament\Resources\SizeUnits;

use App\Filament\Resources\SizeUnits\Pages\CreateSizeUnit;
use App\Filament\Resources\SizeUnits\Pages\EditSizeUnit;
use App\Filament\Resources\SizeUnits\Pages\ListSizeUnits;
use App\Filament\Resources\SizeUnits\Schemas\SizeUnitForm;
use App\Filament\Resources\SizeUnits\Tables\SizeUnitsTable;
use App\Models\SizeUnit;
use BackedEnum;
use App\Filament\Resources\BaseResource;
use App\Support\Roles;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SizeUnitResource extends BaseResource
{
    protected static ?string $model = SizeUnit::class;

    protected static array $allowedRoles = [
        Roles::ADMIN,
    ];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 80;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SizeUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SizeUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSizeUnits::route('/'),
            'create' => CreateSizeUnit::route('/create'),
            'edit' => EditSizeUnit::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
