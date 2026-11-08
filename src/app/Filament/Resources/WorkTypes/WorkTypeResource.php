<?php

namespace App\Filament\Resources\WorkTypes;

use App\Filament\Resources\WorkTypes\Pages\CreateWorkType;
use App\Filament\Resources\WorkTypes\Pages\EditWorkType;
use App\Filament\Resources\WorkTypes\Pages\ListWorkTypes;
use App\Filament\Resources\WorkTypes\Schemas\WorkTypeForm;
use App\Filament\Resources\WorkTypes\Tables\WorkTypesTable;
use App\Filament\Traits\ChecksAdminMenuAccess;
use App\Models\WorkType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkTypeResource extends Resource
{
    use ChecksAdminMenuAccess;
    protected static string $adminMenuKey = 'work_types';
    protected static ?string $model = WorkType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrench;

    protected static ?string $navigationLabel = 'Jenis Pekerjaan';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Jenis Pekerjaan';

    protected static ?string $pluralModelLabel = 'Jenis Pekerjaan';

    public static function form(Schema $schema): Schema
    {
        return WorkTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWorkTypes::route('/'),
            'create' => CreateWorkType::route('/create'),
            'edit'   => EditWorkType::route('/{record}/edit'),
        ];
    }
}
