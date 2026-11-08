<?php

namespace App\Filament\Resources\GarmentCategories;

use App\Filament\Resources\GarmentCategories\Pages\CreateGarmentCategory;
use App\Filament\Resources\GarmentCategories\Pages\EditGarmentCategory;
use App\Filament\Resources\GarmentCategories\Pages\ListGarmentCategories;
use App\Filament\Resources\GarmentCategories\Tables\GarmentCategoriesTable;
use App\Filament\Resources\GarmentCategories\Schemas\GarmentCategoryForm;
use App\Filament\Traits\ChecksAdminMenuAccess;
use App\Models\GarmentCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GarmentCategoryResource extends Resource
{
    use ChecksAdminMenuAccess;

    protected static ?string $model = GarmentCategory::class;
    protected static string $adminMenuKey = 'garment_categories';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Kategori Pakaian';
    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 0;
    protected static ?string $modelLabel = 'Kategori Pakaian';
    protected static ?string $pluralModelLabel = 'Kategori Pakaian';

    public static function form(Schema $schema): Schema
    {
        return GarmentCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GarmentCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListGarmentCategories::route('/'),
            'create' => CreateGarmentCategory::route('/create'),
            'edit'   => EditGarmentCategory::route('/{record}/edit'),
        ];
    }
}
