<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\GarmentCategory;
use App\Models\GarmentType;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Warehouse;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Informasi Produk')
                ->columnSpanFull()
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    TextInput::make('product_code')
                        ->label('Kode Produk')
                        ->disabled()
                        ->dehydrated(true)
                        ->placeholder('Auto-generate saat disimpan')
                        ->columnSpanFull(),

                    Select::make('garment_category_id')
                        ->label('Kategori Produk')
                        ->options(GarmentCategory::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $set('garment_type_id', null);
                            $set('_contoh_pakaian', null);
                            $set('_keterangan', null);
                            if ($state) {
                                $set('product_code', Product::generateCode($state));
                            } else {
                                $set('product_code', '');
                            }
                        })
                        ->afterStateHydrated(function ($state, $set, $record) {
                            if ($record?->garment_type_id) {
                                $gt = GarmentType::find($record->garment_type_id);
                                $set('_contoh_pakaian', $gt?->contoh_pakaian);
                                $set('_keterangan', $gt?->keterangan);
                            }
                        }),

                    Select::make('garment_type_id')
                        ->label('Sub Kategori / Jenis Pakaian')
                        ->options(fn ($get) => GarmentType::active()
                            ->when($get('garment_category_id'), fn ($q, $k) => $q->where('garment_category_id', $k))
                            ->orderBy('sub_kategori')
                            ->orderBy('contoh_pakaian')
                            ->get()
                            ->mapWithKeys(fn ($t) => [$t->id => "{$t->sub_kategori} — {$t->contoh_pakaian}"])
                            ->toArray()
                        )
                        ->searchable()
                        ->nullable()
                        ->disabled(fn ($get) => !$get('garment_category_id'))
                        ->placeholder(fn ($get) => $get('garment_category_id') ? 'Pilih jenis pakaian' : 'Pilih kategori dulu')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $gt = GarmentType::find($state);
                                $set('_contoh_pakaian', $gt?->contoh_pakaian);
                                $set('_keterangan', $gt?->keterangan);
                            } else {
                                $set('_contoh_pakaian', null);
                                $set('_keterangan', null);
                            }
                        })
                        ->dehydrated(true),

                    TextInput::make('_contoh_pakaian')
                        ->label('Contoh Pakaian')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Terisi otomatis dari jenis pakaian'),

                    Textarea::make('_keterangan')
                        ->label('Keterangan Detail')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Terisi otomatis dari jenis pakaian')
                        ->rows(2)
                        ->columnSpanFull(),

                    TextInput::make('nama')
                        ->label('Nama Produk')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('deskripsi')
                        ->label('Deskripsi Produk')
                        ->placeholder('Tulis deskripsi produk (bahan, detail, ukuran fit, dll)...')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    TextInput::make('jumlah')
                        ->label('Jumlah (item)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    TextInput::make('harga')
                        ->label('Harga (Rp)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->default(0),

                    Select::make('warehouse_id')
                        ->label('Gudang Awal')
                        ->helperText('Gudang default saat unit baru dibuat. Unit bisa dipindah ke gudang lain setelahnya.')
                        ->options(Warehouse::active()->orderBy('nama')->pluck('nama', 'id'))
                        ->searchable()
                        ->nullable()
                        ->placeholder('Pilih gudang (opsional)')
                        ->columnSpanFull(),

                    TextInput::make('video_url')
                        ->label('Link Video (TikTok / Instagram)')
                        ->url()
                        ->placeholder('https://www.tiktok.com/... atau https://www.instagram.com/...')
                        ->columnSpanFull(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'aktif'    => 'Aktif',
                            'nonaktif' => 'Nonaktif',
                            'habis'    => 'Habis',
                        ])
                        ->default('aktif')
                        ->required(),
                ]),

            Section::make('Stok per Ukuran')
                ->columnSpanFull()
                ->description('Opsional — saat membuat produk baru: assign ukuran ke unit yang akan dibuat. Saat edit produk: assign ukuran ke unit stok yang belum punya ukuran.')
                ->collapsed()
                ->schema([
                    Repeater::make('size_distribution')
                        ->label(false)
                        ->schema([
                            Select::make('product_size_id')
                                ->label('Ukuran')
                                ->options(fn () => ProductSize::aktif()->pluck('nama', 'id')->toArray())
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('jumlah')
                                ->label('Jumlah Unit')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->rules(['integer', 'min:1'])
                                ->extraAttributes(['min' => '1'])
                                ->columnSpan(1),
                        ])
                        ->columns(2)
                        ->addActionLabel('Tambah Ukuran')
                        ->defaultItems(0)
                        ->dehydrated(false)
                        ->helperText(function ($get, $state) {
                            $jumlahProduk = (int) ($get('jumlah') ?? 0);
                            if (empty($state)) {
                                return 'Setiap baris = satu ukuran + jumlah unit. Misal: S×5, M×10, L×8.';
                            }
                            $total = array_sum(array_map(fn ($r) => (int) ($r['jumlah'] ?? 0), $state));
                            if ($total > $jumlahProduk) {
                                return "⚠️ Total ukuran ({$total}) melebihi jumlah produk ({$jumlahProduk}). Kurangi jumlah atau sesuaikan kolom Jumlah di atas.";
                            }
                            return "Total: {$total} unit dari {$jumlahProduk} yang tersedia.";
                        }),
                ]),

            Section::make('Foto & Warna Produk')
                ->columnSpanFull()
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    Section::make('Foto Produk')
                        ->description('Minimal 1 foto, maksimal 5 foto')
                        ->columnSpan(1)
                        ->schema([
                            FileUpload::make('photos')
                                ->label('Upload Foto')
                                ->image()
                                ->multiple()
                                ->minFiles(1)
                                ->maxFiles(5)
                                ->maxSize(5120)
                                ->disk('public')
                                ->directory('products')
                                ->reorderable()
                                ->helperText('Minimal 1 foto, maksimal 5 foto. Maks 5MB per foto. Bisa diurutkan ulang.')
                                ->dehydrated(false),
                        ]),

                    Section::make('Palet Warna Produk')
                        ->description('Klik pada foto untuk ambil warna, lalu beri nama tiap warna')
                        ->columnSpan(1)
                        ->schema([
                            View::make('filament.resources.products.color-picker')
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
