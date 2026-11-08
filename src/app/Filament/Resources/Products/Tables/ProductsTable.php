<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductUnit;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photos.photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->width(60)
                    ->height(60)
                    ->defaultImageUrl(url('/images/no-image.png'))
                    ->circular(false),

                TextColumn::make('product_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('nama')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('garmentCategory.nama')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('stok_aktif')
                    ->label('Stok Tersedia')
                    ->getStateUsing(fn (Product $record) => $record->getStokCount())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),

                TextColumn::make('unit_aktif')
                    ->label('Dalam Proses')
                    ->getStateUsing(fn (Product $record) => $record->getUnitAktifAttribute())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                TextColumn::make('jumlah')
                    ->label('Total Unit')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'aktif'    => 'success',
                        'nonaktif' => 'gray',
                        'habis'    => 'danger',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('garment_category_id')
                    ->label('Kategori')
                    ->relationship('garmentCategory', 'nama'),

                SelectFilter::make('status')
                    ->options([
                        'aktif'    => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                        'habis'    => 'Habis',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),

                Action::make('edit_size')
                    ->label('Edit Size')
                    ->icon('heroicon-o-swatch')
                    ->color('info')
                    ->modalHeading(fn (Product $record) => "Edit Ukuran Unit — {$record->nama}")
                    ->modalDescription('Assign ukuran ke setiap unit yang masih berstatus Stok.')
                    ->modalWidth('2xl')
                    ->fillForm(function (Product $record): array {
                        $units = ProductUnit::with('size')
                            ->where('product_id', $record->id)
                            ->where('status', 'stok')
                            ->orderBy('unit_code')
                            ->get();

                        return [
                            'unit_sizes' => $units->map(fn ($u) => [
                                'unit_code'       => $u->unit_code,
                                'product_size_id' => $u->product_size_id,
                            ])->toArray(),
                        ];
                    })
                    ->form([
                        Repeater::make('unit_sizes')
                            ->label('Unit Stok')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('unit_code')
                                    ->label('Kode Unit')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(1),

                                Select::make('product_size_id')
                                    ->label('Ukuran')
                                    ->options(fn () => ProductSize::aktif()->pluck('nama', 'id')->toArray())
                                    ->placeholder('Tanpa ukuran')
                                    ->nullable()
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $rows = $data['unit_sizes'] ?? [];
                        $updated = 0;
                        foreach ($rows as $row) {
                            $affected = ProductUnit::where('unit_code', $row['unit_code'])
                                ->where('product_id', $record->id)
                                ->update(['product_size_id' => $row['product_size_id'] ?: null]);
                            $updated += $affected;
                        }

                        Notification::make()
                            ->title("{$updated} unit berhasil diupdate ukurannya")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription(fn (Product $record) => $record->hasActiveUnits()
                        ? '⚠️ PERHATIAN: Produk ini memiliki ' . $record->getUnitAktifAttribute() . ' unit yang sedang dipesan/dikirim/dalam perjalanan. Menghapus produk ini akan menghapus semua data unit tersebut secara permanen. Yakin ingin melanjutkan?'
                        : 'Apakah Anda yakin ingin menghapus produk ini? Semua data unit dan foto akan ikut terhapus.')
                    ->modalHeading(fn (Product $record) => $record->hasActiveUnits()
                        ? 'Hapus Produk — Ada Unit Aktif!'
                        : 'Hapus Produk'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
