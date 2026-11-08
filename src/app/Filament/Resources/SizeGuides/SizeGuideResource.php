<?php

namespace App\Filament\Resources\SizeGuides;

use App\Filament\Resources\SizeGuides\Pages\ListSizeGuides;
use App\Filament\Resources\SizeGuides\Pages\CreateSizeGuide;
use App\Filament\Resources\SizeGuides\Pages\EditSizeGuide;
use App\Filament\Traits\ChecksAdminMenuAccess;
use App\Models\SizeGuide;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SizeGuideResource extends Resource
{
    use ChecksAdminMenuAccess;
    protected static string $adminMenuKey = 'size_guides';
    protected static ?string $model = SizeGuide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Panduan Ukuran';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Panduan Ukuran';

    protected static ?string $pluralModelLabel = 'Panduan Ukuran';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\SizeGuides\Schemas\SizeGuideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\SizeGuides\Tables\SizeGuidesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSizeGuides::route('/'),
            'create' => CreateSizeGuide::route('/create'),
            'edit'   => EditSizeGuide::route('/{record}/edit'),
        ];
    }
}
