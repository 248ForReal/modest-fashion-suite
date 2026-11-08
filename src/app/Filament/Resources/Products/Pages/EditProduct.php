<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductColor;
use App\Models\ProductPhoto;
use App\Models\ProductSize;
use App\Models\ProductUnit;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public array $colorData = ['json' => '[]'];

    public function updateColors(string $json): void
    {
        $this->colorData['json'] = $json;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit_ukuran_unit')
                ->label('Edit Ukuran Unit')
                ->icon('heroicon-o-swatch')
                ->color('info')
                ->modalHeading(fn () => 'Edit Ukuran Unit — ' . $this->record->nama)
                ->modalDescription('Ganti ukuran untuk setiap unit stok. Unit yang sedang dipesan/dikirim tidak ditampilkan.')
                ->modalWidth('2xl')
                ->fillForm(function (): array {
                    $units = ProductUnit::with('size')
                        ->where('product_id', $this->record->id)
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
                            TextInput::make('unit_code')
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
                ->action(function (array $data): void {
                    $rows    = $data['unit_sizes'] ?? [];
                    $updated = 0;
                    foreach ($rows as $row) {
                        $affected = ProductUnit::where('unit_code', $row['unit_code'])
                            ->where('product_id', $this->record->id)
                            ->update(['product_size_id' => $row['product_size_id'] ?: null]);
                        $updated += $affected;
                    }

                    Notification::make()
                        ->title("{$updated} unit berhasil diupdate ukurannya")
                        ->success()
                        ->send();
                }),

            DeleteAction::make()->successNotificationTitle('Produk berhasil dihapus'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['photos'] = $this->record->photos->pluck('photo_path')->toArray();

        // Load existing colors ke colorData untuk dikirim ke Alpine
        $existing = $this->record->colors->map(fn ($c) => [
            'hex'  => $c->hex,
            'nama' => $c->nama ?? '',
        ])->values()->toArray();
        $this->colorData['json'] = json_encode($existing);

        return $data;
    }

    protected function afterSave(): void
    {
        $rawState         = $this->form->getRawState();
        $sizeDistribution = array_values(array_filter(
            $rawState['size_distribution'] ?? [],
            fn ($row) => ($row['product_size_id'] ?? null) && (int) ($row['jumlah'] ?? 0) >= 1
        ));
        $sizeDistribution = array_map(fn ($row) => array_merge($row, ['jumlah' => max(1, (int) $row['jumlah'])]), $sizeDistribution);

        // Di EditProduct, size distribution = assign ukuran ke unit stok yang belum punya ukuran
        // Bukan tambah unit baru
        if (!empty($sizeDistribution)) {
            $totalSize = array_sum(array_column($sizeDistribution, 'jumlah'));

            // Unit stok yang belum punya ukuran
            $unassigned = ProductUnit::where('product_id', $this->record->id)
                ->where('status', 'stok')
                ->whereNull('product_size_id')
                ->orderBy('unit_code')
                ->get();

            $totalUnassigned = $unassigned->count();

            if ($totalSize > $totalUnassigned) {
                Notification::make()
                    ->title("Total ukuran ({$totalSize}) melebihi unit stok tanpa ukuran ({$totalUnassigned}). Assign hanya dilakukan sebatas unit yang tersedia.")
                    ->warning()
                    ->persistent()
                    ->send();
            }

            // Assign ukuran ke unit satu per satu sesuai distribusi
            $pointer = 0;
            foreach ($sizeDistribution as $row) {
                $sizeId = $row['product_size_id'];
                $qty    = (int) $row['jumlah'];
                for ($i = 0; $i < $qty; $i++) {
                    if ($pointer >= $totalUnassigned) break 2;
                    $unassigned[$pointer]->update(['product_size_id' => $sizeId]);
                    $pointer++;
                }
            }
        }

        // syncUnits tetap dipanggil untuk tambah unit jika jumlah dinaikkan (tanpa ukuran)
        $this->record->syncUnits(
            $this->record->jumlah,
            $this->record->warehouse_id,
            [] // distribusi ukuran sudah ditangani di atas
        );

        $photos   = $rawState['photos'] ?? [];
        $existing = $this->record->photos->pluck('photo_path')->toArray();
        $new      = array_values($photos);

        $toDelete = array_diff($existing, $new);
        foreach ($toDelete as $path) {
            Storage::disk('public')->delete($path);
            ProductPhoto::where('product_id', $this->record->id)->where('photo_path', $path)->delete();
        }

        $toAdd     = array_diff($new, $existing);
        $maxUrutan = $this->record->photos()->max('urutan') ?? -1;
        foreach (array_values($toAdd) as $i => $path) {
            $this->compressAndSave($this->record->id, $path, $maxUrutan + $i + 1);
        }

        foreach ($new as $urutan => $path) {
            ProductPhoto::where('product_id', $this->record->id)
                ->where('photo_path', $path)
                ->update(['urutan' => $urutan]);
        }

        $this->saveColors($this->record->id);
    }

    private function saveColors(int $productId): void
    {
        $colors = json_decode($this->colorData['json'], true);
        if (!is_array($colors)) return;

        ProductColor::where('product_id', $productId)->delete();
        foreach ($colors as $i => $c) {
            if (empty($c['hex'])) continue;
            ProductColor::create([
                'product_id' => $productId,
                'hex'        => strtoupper($c['hex']),
                'nama'       => $c['nama'] ?? null,
                'urutan'     => $i,
            ]);
        }
    }

    private function compressAndSave(int $productId, string $path, int $urutan): void
    {
        $fullPath = Storage::disk('public')->path($path);
        if (!file_exists($fullPath)) return;

        $manager = new ImageManager(new Driver());
        $image   = $manager->read($fullPath);
        $image->scaleDown(width: 1280);
        $image->toJpeg(80)->save($fullPath);

        ProductPhoto::create([
            'product_id' => $productId,
            'photo_path' => $path,
            'urutan'     => $urutan,
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Produk berhasil diupdate';
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.resources.products.pages.edit-product-units', ['page' => $this]);
    }

    public function getUnitsForProduct(): array
    {
        return ProductUnit::with(['warehouse', 'size'])
            ->where('product_id', $this->record->id)
            ->orderBy('status')
            ->orderBy('unit_code')
            ->get()
            ->map(fn ($u) => [
                'unit_code'    => $u->unit_code,
                'status'       => $u->status,
                'status_label' => ProductUnit::STATUSES[$u->status] ?? $u->status,
                'status_color' => ProductUnit::STATUS_COLORS[$u->status] ?? 'gray',
                'gudang'       => $u->warehouse?->nama ?? '—',
                'ukuran'       => $u->size?->nama ?? '—',
                'posisi'       => $u->posisi ?? '',
                'updated_at'   => $u->updated_at->format('d M Y H:i'),
            ])
            ->toArray();
    }
}
