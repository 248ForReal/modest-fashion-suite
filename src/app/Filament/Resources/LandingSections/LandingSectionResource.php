<?php

namespace App\Filament\Resources\LandingSections;

use App\Filament\Resources\LandingSections\Pages\EditLandingSection;
use App\Filament\Resources\LandingSections\Pages\ListLandingSections;
use App\Filament\Resources\LandingSections\Schemas\LandingSectionForm;
use App\Filament\Resources\LandingSections\Tables\LandingSectionsTable;
use App\Models\LandingSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LandingSectionResource extends Resource
{
    protected static ?string $model = LandingSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel  = 'Landing Page';
    protected static ?string $modelLabel       = 'Section';
    protected static ?string $pluralModelLabel = 'Landing Page';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    // Section sudah fixed 9 buah (seeded) — tidak boleh tambah/hapus row baru dari admin
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LandingSectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LandingSectionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLandingSections::route('/'),
            'edit'  => EditLandingSection::route('/{record}/edit'),
        ];
    }
}
