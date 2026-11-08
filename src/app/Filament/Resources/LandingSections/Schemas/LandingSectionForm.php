<?php

namespace App\Filament\Resources\LandingSections\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class LandingSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Pengaturan Section')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('key')
                        ->label('Key')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Identitas internal section, tidak bisa diubah.'),

                    Select::make('display_type')
                        ->label('Tipe Tampilan')
                        ->options([
                            'static'   => 'Statis',
                            'grid'     => 'Grid',
                            'carousel' => 'Carousel (geser)',
                        ])
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Tampilkan di Landing Page')
                        ->default(true)
                        ->inline(false),
                ]),

            Section::make('Konten Section')
                ->description('Isi konten dalam Bahasa Inggris (default) dan Bahasa Indonesia. Kosongkan field yang tidak dipakai section ini.')
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    Tabs::make('Bahasa')
                        ->columnSpanFull()
                        ->contained(false)
                        ->tabs([
                            Tab::make('English')
                                ->schema([
                                    TextInput::make('eyebrow_en')->label('Eyebrow / Label Kecil')->columnSpanFull(),
                                    TextInput::make('title_en')->label('Judul')->columnSpanFull(),
                                    Textarea::make('subtitle_en')->label('Subjudul')->rows(2)->columnSpanFull(),
                                    Textarea::make('body_en')->label('Isi / Paragraf')->rows(5)->columnSpanFull(),
                                    TextInput::make('cta_label_en')->label('Label Tombol CTA')->columnSpanFull(),
                                ]),
                            Tab::make('Bahasa Indonesia')
                                ->schema([
                                    TextInput::make('eyebrow_id')->label('Eyebrow / Label Kecil')->columnSpanFull(),
                                    TextInput::make('title_id')->label('Judul')->columnSpanFull(),
                                    Textarea::make('subtitle_id')->label('Subjudul')->rows(2)->columnSpanFull(),
                                    Textarea::make('body_id')->label('Isi / Paragraf')->rows(5)->columnSpanFull(),
                                    TextInput::make('cta_label_id')->label('Label Tombol CTA')->columnSpanFull(),
                                ]),
                        ]),

                    TextInput::make('cta_url')
                        ->label('URL Tombol CTA')
                        ->helperText('Contoh: /produk atau https://wa.me/62812xxxxxxx')
                        ->columnSpanFull(),
                ]),

            Section::make('Item / Slide dalam Section')
                ->description('Foto, kartu, slide carousel, produk, partner, atau testimoni dalam section ini. Urutan bisa digeser (drag).')
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->label(false)
                        ->reorderable()
                        ->reorderableWithButtons()
                        ->orderColumn('urutan')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? $state['label_en'] ?? 'Item baru')
                        ->addActionLabel('Tambah Item')
                        ->columnSpanFull()
                        ->schema([
                            FileUpload::make('image_path')
                                ->label('Gambar')
                                ->image()
                                ->disk('public')
                                ->directory('landing')
                                ->visibility('public')
                                ->columnSpanFull()
                                ->imageEditor()
                                ->openable()
                                ->downloadable()
                                ->panelLayout('integrated')
                                ->imagePreviewHeight('200'),

                            Toggle::make('is_active')
                                ->label('Aktif')
                                ->default(true)
                                ->inline(false)
                                ->columnSpanFull(),

                            TextInput::make('label_en')->label('Label (EN)')->helperText('Mis. nama warna, kategori, nomor tahap'),
                            TextInput::make('label_id')->label('Label (ID)'),

                            TextInput::make('title_en')->label('Judul (EN)'),
                            TextInput::make('title_id')->label('Judul (ID)'),

                            Textarea::make('body_en')->label('Deskripsi (EN)')->rows(3),
                            Textarea::make('body_id')->label('Deskripsi (ID)')->rows(3),

                            TextInput::make('link_url')
                                ->label('Link (opsional)')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ]),
        ]);
    }
}
