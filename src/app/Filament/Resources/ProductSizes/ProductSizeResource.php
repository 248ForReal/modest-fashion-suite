<?php

namespace App\Filament\Resources\ProductSizes;

use App\Filament\Resources\ProductSizes\Pages\ListProductSizes;
use App\Filament\Resources\ProductSizes\Pages\CreateProductSize;
use App\Filament\Resources\ProductSizes\Pages\EditProductSize;
use App\Filament\Traits\ChecksAdminMenuAccess;
use App\Models\ProductSize;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductSizeResource extends Resource
{
    use ChecksAdminMenuAccess;
    protected static string $adminMenuKey = 'product_sizes';
    protected static ?string $model = ProductSize::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $navigationLabel = 'Ukuran Produk';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Ukuran';

    protected static ?string $pluralModelLabel = 'Ukuran Produk';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\ProductSizes\Schemas\ProductSizeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\ProductSizes\Tables\ProductSizesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProductSizes::route('/'),
            'create' => CreateProductSize::route('/create'),
            'edit'   => EditProductSize::route('/{record}/edit'),
        ];
    }
}
