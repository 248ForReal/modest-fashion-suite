<?php

namespace App\Filament\Resources\WorkAssignments\Schemas;

use App\Models\GarmentCategory;
use App\Models\GarmentType;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WorkAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $userOptions = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['magang', 'siswa', 'education']))
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $kategoriOptions = GarmentCategory::where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();

        $workTypeOptions = WorkType::active()
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($w) => [$w->id => "{$w->nama} ({$w->kategori_nilai})"])
            ->toArray();

        return $schema
            ->components([
                Section::make('Jenis Tugas')
                    ->schema([
                        Radio::make('type')
                            ->label('Model Penugasan')
                            ->options([
                                'personal' => 'Personal — assign langsung ke 1 user',
                                'pool'     => 'Pool — dilempar ke semua, siapa cepat klaim',
                            ])
                            ->default('personal')
                            ->required()
                            ->live()
                            ->inline(),

                        Select::make('assigned_to')
                            ->label('Ditugaskan ke')
                            ->options($userOptions)
                            ->searchable()
                            ->required(fn ($get) => $get('type') === 'personal')
                            ->visible(fn ($get) => $get('type') === 'personal')
                            ->helperText('Hanya user aktif dengan role magang/siswa/education')
                            ->validationMessages(['required' => 'User wajib dipilih untuk tugas personal.']),
                    ]),

                Section::make('Detail Tugas')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Select::make('garment_kategori')
                            ->label('Kategori Pakaian')
                            ->options($kategoriOptions)
                            ->searchable()
                            ->required()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($set, $record) {
                                $categoryId = $record?->garmentType?->garment_category_id
                                    ?? $record?->garmentType?->garmentCategory?->id;
                                if ($categoryId) {
                                    $set('garment_kategori', $categoryId);
                                }
                            })
                            ->afterStateUpdated(fn ($set) => $set('garment_type_id', null)),

                        Select::make('garment_type_id')
                            ->label('Jenis Pakaian')
                            ->options(fn ($get) => GarmentType::active()
                                ->when($get('garment_kategori'), fn ($q, $k) => $q->where('garment_category_id', $k))
                                ->orderBy('contoh_pakaian')
                                ->pluck('contoh_pakaian', 'id')
                                ->toArray()
                            )
                            ->searchable()
                            ->required()
                            ->disabled(fn ($get) => !$get('garment_kategori'))
                            ->dehydrated(true)
                            ->placeholder(fn ($get) => $get('garment_kategori') ? 'Pilih jenis pakaian' : 'Pilih kategori dulu'),

                        Select::make('work_type_id')
                            ->label('Jenis Pekerjaan')
                            ->options($workTypeOptions)
                            ->searchable()
                            ->required(),

                        TextInput::make('target_jumlah')
                            ->label('Target Jumlah (item)')
                            ->required()
                            ->numeric()
                            ->minValue(1),

                        DatePicker::make('deadline')
                            ->label('Deadline')
                            ->minDate(now()->toDateString())
                            ->native(true),

                        Textarea::make('instruksi')
                            ->label('Instruksi / Catatan')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('foto_instruksi')
                            ->label('Foto Instruksi (opsional)')
                            ->image()
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('work-instructions')
                            ->helperText('Maks 5MB. JPG/PNG/WEBP.')
                            ->columnSpanFull(),

                        TextInput::make('link_instruksi')
                            ->label('Link Referensi (opsional)')
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('Link video tutorial, Google Drive, dll.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Produk Terkait')
                    ->description('Jika tugas ini menghasilkan produk fisik, pilih produk yang stoknya akan bertambah otomatis saat tugas disetujui.')
                    ->collapsed()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(
                                Product::where('status', 'aktif')
                                    ->orderBy('nama')
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [$p->id => "[{$p->product_code}] {$p->nama}"])
                                    ->toArray()
                            )
                            ->searchable()
                            ->nullable()
                            ->placeholder('Pilih produk (opsional)')
                            ->helperText('Stok produk ini akan bertambah otomatis saat tugas disetujui.'),

                        TextInput::make('hasil_jumlah')
                            ->label('Jumlah Unit Dihasilkan')
                            ->numeric()
                            ->minValue(1)
                            ->nullable()
                            ->placeholder('Kosongkan = ikuti target jumlah')
                            ->helperText('Berapa unit yang akan ditambahkan ke stok. Default = target jumlah tugas.'),
                    ]),
            ]);
    }
}
