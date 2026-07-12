<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Support\Roles;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 10;
    
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->hasRole(Roles::SALES_AGENT)) {
            return $query->where('created_by', $user->id);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole([
            Roles::ADMIN,
            Roles::SALES_MANAGER,
            Roles::SALES_AGENT,
        ]) ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole([
            Roles::ADMIN,
            Roles::SALES_MANAGER,
        ])) {
            return true;
        }

        if (
            $user->hasRole(Roles::SALES_AGENT)
            && $record->created_by === $user->id
            && $record->status === 'draft'
        ) {
            return true;
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole([
            Roles::ADMIN,
            Roles::SALES_MANAGER,
        ]) ?? false;
    }
}