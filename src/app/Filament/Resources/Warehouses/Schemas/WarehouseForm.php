<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')
                ->label('Nama Gudang')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true)
                ->columnSpanFull(),

            Textarea::make('alamat')
                ->label('Alamat')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),

            TextInput::make('latitude')
                ->label('Latitude')
                ->numeric()
                ->placeholder('-6.2088')
                ->helperText('Koordinat GPS — bisa kosong'),

            TextInput::make('longitude')
                ->label('Longitude')
                ->numeric()
                ->placeholder('106.8456')
                ->helperText('Koordinat GPS — bisa kosong'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'aktif'    => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ])
                ->default('aktif')
                ->required(),
        ]);
    }
}
