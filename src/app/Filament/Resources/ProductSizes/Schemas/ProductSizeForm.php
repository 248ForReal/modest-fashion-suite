<?php

namespace App\Filament\Resources\ProductSizes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductSizeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ukuran')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    TextInput::make('nama')
                        ->label('Nama Ukuran')
                        ->placeholder('S, M, L, XL, Free Size, ...')
                        ->required()
                        ->maxLength(20),

                    TextInput::make('urutan')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Angka kecil = tampil lebih dulu'),

                    Toggle::make('aktif')
                        ->label('Aktif')
                        ->default(true),
                ]),
        ]);
    }
}
