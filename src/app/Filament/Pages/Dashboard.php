<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Dashboard';
    protected static ?string $navigationLabel = 'Dashboard';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function getVisibleWidgets(): array
    {
        $user = auth()->user();
        if (!$user?->hasAnyRole(['super_admin', 'admin'])) {
            return [];
        }
        return parent::getVisibleWidgets();
    }
}
