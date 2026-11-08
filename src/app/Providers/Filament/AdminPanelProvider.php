<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('Atelier')
            ->brandLogo(asset('images/logo.svg'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/logo.svg'))
            ->darkMode(false)
            ->colors([
                'primary'   => Color::hex('#F4A95A'),
                'secondary' => Color::hex('#E8816A'),
                'tertiary'  => Color::hex('#C4644A'),
                'gray'      => Color::Slate,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups()
            ->navigationGroups([
                NavigationGroup::make('Master Data')->collapsed(),
                NavigationGroup::make('Absensi')->collapsed(),
                NavigationGroup::make('Inventory')->collapsed(),
                NavigationGroup::make('Manajemen Kerja')->collapsed(),
                NavigationGroup::make('Pengaturan')->collapsed(),
            ])
            ->renderHook(
                'panels::styles.after',
                fn () => '
<style>
/* Sidebar putih bersih */
.fi-sidebar {
    background-color: #ffffff !important;
    border-right: 1px solid #f1f5f9 !important;
}
.fi-sidebar-nav {
    background-color: #ffffff !important;
}
/* Header sidebar (logo area) */
.fi-sidebar-header {
    background-color: #ffffff !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
/* Content area sedikit abu */
.fi-main {
    background-color: #f8fafc !important;
}
/* Active nav item */
.fi-sidebar-item-active .fi-sidebar-item-button {
    background-color: #fff7ed !important;
    color: #ea580c !important;
}
/* Nav item hover */
.fi-sidebar-item-button:hover {
    background-color: #f8fafc !important;
}
/* Group label */
.fi-sidebar-group-label {
    color: #94a3b8 !important;
}
</style>
'
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\ProductStatsWidget::class,
                \App\Filament\Widgets\OperationalDashboardWidget::class,
                \App\Filament\Widgets\AttendanceSettingsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
