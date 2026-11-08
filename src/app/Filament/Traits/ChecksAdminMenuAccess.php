<?php

namespace App\Filament\Traits;

use App\Filament\Pages\WorkSettings;

trait ChecksAdminMenuAccess
{
    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->hasAnyRole(['super_admin', 'admin'])) return true;

        $role = $user->roles->first()?->name;
        if (!$role) return false;

        return WorkSettings::canAccessMenu($role, static::$adminMenuKey ?? '');
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
