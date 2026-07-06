<?php

namespace App\Filament\Resources;

use App\Support\Roles;
use Filament\Resources\Resource;

abstract class BaseResource extends Resource
{
    protected static array $allowedRoles = [
        Roles::ADMIN,
    ];

    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->hasAnyRole(static::$allowedRoles);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}