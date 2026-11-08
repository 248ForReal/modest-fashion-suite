<?php

namespace App\Filament\Resources\GarmentCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GarmentCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),
            Toggle::make('aktif')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
