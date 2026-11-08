<?php

namespace App\Filament\Resources\GarmentTypes;

use App\Filament\Resources\GarmentTypes\Pages\CreateGarmentType;
use App\Filament\Resources\GarmentTypes\Pages\EditGarmentType;
use App\Filament\Resources\GarmentTypes\Pages\ListGarmentTypes;
use App\Filament\Resources\GarmentTypes\Schemas\GarmentTypeForm;
use App\Filament\Resources\GarmentTypes\Tables\GarmentTypesTable;
use App\Filament\Traits\ChecksAdminMenuAccess;
use App\Models\GarmentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GarmentTypeResource extends Resource
{
    use ChecksAdminMenuAccess;
    protected static string $adminMenuKey = 'garment_types';
    protected static ?string $model = GarmentType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Jenis Pakaian';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Jenis Pakaian';

    protected static ?string $pluralModelLabel = 'Jenis Pakaian';

    public static function form(Schema $schema): Schema
    {
        return GarmentTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GarmentTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListGarmentTypes::route('/'),
            'create' => CreateGarmentType::route('/create'),
            'edit'   => EditGarmentType::route('/{record}/edit'),
        ];
    }
}
