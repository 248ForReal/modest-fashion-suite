<?php

namespace App\Filament\Resources\GarmentTypes\Schemas;

use App\Models\GarmentCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GarmentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pakaian')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Select::make('garment_category_id')
                            ->label('Kategori')
                            ->options(GarmentCategory::where('aktif', true)->pluck('nama', 'id'))
                            ->required()
                            ->searchable(),
                        TextInput::make('sub_kategori')
                            ->label('Sub Kategori')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('contoh_pakaian')
                            ->label('Contoh Pakaian')
                            ->required()
                            ->maxLength(150)
                            ->columnSpanFull(),
                        Textarea::make('keterangan')
                            ->label('Keterangan Detail')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Nilai Poin')
                    ->description('Nilai absolut per item pakaian')
                    ->columns(['default' => 1, 'sm' => 3])
                    ->schema([
                        TextInput::make('nilai_jahit')
                            ->label('Nilai Jahit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('nilai_desain')
                            ->label('Nilai Desain')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('nilai_pola')
                            ->label('Nilai Pola')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
