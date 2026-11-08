<?php

namespace App\Filament\Resources\WorkTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Pekerjaan')
                            ->required()
                            ->maxLength(100),
                        Select::make('kategori_nilai')
                            ->label('Kategori Nilai')
                            ->options([
                                'jahit'  => 'Jahit',
                                'desain' => 'Desain',
                                'pola'   => 'Pola',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('bobot_persen')
                            ->label('Bobot (%)')
                            ->helperText('Persentase dari nilai dasar kategori (total per kategori = 100%)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(100),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
