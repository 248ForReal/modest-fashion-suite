<?php

namespace App\Filament\Resources\SizeGuides\Schemas;

use App\Models\GarmentCategory;
use App\Models\ProductSize;
use App\Models\SizeGuide;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SizeGuideForm
{
    public static function configure(Schema $schema): Schema
    {
        $labels = SizeGuide::dimensiLabels();

        return $schema->components([
            Section::make('Panduan Ukuran')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    Select::make('garment_category_id')
                        ->label('Kategori Pakaian')
                        ->options(GarmentCategory::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                        ->searchable()
                        ->required(),

                    Select::make('product_size_id')
                        ->label('Ukuran')
                        ->options(ProductSize::where('aktif', true)->orderBy('urutan')->pluck('nama', 'id'))
                        ->required(),
                ]),

            Section::make('Dimensi (cm)')
                ->description('Isi dimensi yang relevan untuk kategori dan ukuran ini. Kosongkan field yang tidak relevan.')
                ->schema([
                    KeyValue::make('dimensi')
                        ->label(false)
                        ->keyLabel('Dimensi')
                        ->valueLabel('Nilai (cm)')
                        ->keyPlaceholder('cth: lingkar_dada')
                        ->valuePlaceholder('cth: 96')
                        ->addActionLabel('Tambah Dimensi')
                        ->helperText(
                            'Field umum: ' . implode(', ', array_keys($labels))
                        )
                        ->columnSpanFull()
                        ->reorderable(),
                ]),
        ]);
    }
}
