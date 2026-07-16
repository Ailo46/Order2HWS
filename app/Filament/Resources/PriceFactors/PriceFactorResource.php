<?php

namespace App\Filament\Resources\PriceFactors;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\PriceFactors\Pages\EditPriceFactor;
use App\Filament\Resources\PriceFactors\Pages\ListPriceFactors;
use App\Filament\Resources\PriceFactors\Schemas\PriceFactorForm;
use App\Filament\Resources\PriceFactors\Tables\PriceFactorsTable;
use App\Models\Setting;
use App\Support\Roles;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PriceFactorResource extends BaseResource
{
    protected static ?string $model = Setting::class;

    protected static array $allowedRoles = [
        Roles::ADMIN,
    ];

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedCurrencyPound;

    protected static string|\UnitEnum|null $navigationGroup =
        '⚙️ Settings';

    protected static ?string $navigationLabel =
        'Price Factors';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return PriceFactorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceFactorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceFactors::route('/'),
            'edit' => EditPriceFactor::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()

            ->where('category', 'pricing')

            ->orderBy('key');
    }
}