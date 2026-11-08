<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\GarmentType;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductColor;
use App\Models\ProductPhoto;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public array $colorData = ['json' => '[]'];

    public function updateColors(string $json): void
    {
        $this->colorData['json'] = $json;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        if (empty($data['product_code']) && !empty($data['garment_category_id'])) {
            $data['product_code'] = Product::generateCode($data['garment_category_id']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $rawState         = $this->form->getRawState();
        $sizeDistribution = array_values(array_filter(
            $rawState['size_distribution'] ?? [],
            fn ($row) => ($row['product_size_id'] ?? null) && (int) ($row['jumlah'] ?? 0) >= 1
        ));
        // Paksa jumlah jadi integer positif, buang yang masih <= 0 setelah cast
        $sizeDistribution = array_map(fn ($row) => array_merge($row, ['jumlah' => max(1, (int) $row['jumlah'])]), $sizeDistribution);

        // Validasi: total size distribution tidak boleh melebihi jumlah produk
        if (!empty($sizeDistribution)) {
            $totalSize = array_sum(array_column($sizeDistribution, 'jumlah'));
            if ($totalSize > $this->record->jumlah) {
                Notification::make()
                    ->title("Total ukuran ({$totalSize}) melebihi jumlah produk ({$this->record->jumlah}). Size distribution diabaikan, stok dibuat tanpa ukuran.")
                    ->warning()
                    ->persistent()
                    ->send();
                $sizeDistribution = [];
            }
        }

        $this->record->syncUnits(
            $this->record->jumlah,
            $this->record->warehouse_id,
            $sizeDistribution
        );

        $photos = $this->form->getRawState()['photos'] ?? [];
        foreach (array_values($photos) as $index => $path) {
            $this->compressAndSave($this->record->id, $path, $index);
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Produk berhasil ditambahkan';
    }

    // ── Mode AI: simpan produk dari hasil parse AI ──

    public function saveFromAi(array $data): void
    {
        $nama        = trim($data['nama'] ?? '');
        $kategoriId  = (int) ($data['kategori_id'] ?? 0);
        $harga       = (float) ($data['harga'] ?? 0);
        $videoUrl    = $data['video_url'] ?? null;
        $deskripsi   = trim($data['deskripsi'] ?? '') ?: null;
        $warna       = $data['warna'] ?? [];
        $ukuranData  = $data['ukuran'] ?? [];
        $warehouseId = ($data['warehouse_id'] ?? null) ? (int) $data['warehouse_id'] : null;
        $jenisNama   = trim($data['jenis'] ?? '');

        if (!$nama || !$kategoriId) {
            Notification::make()->title('Data tidak lengkap. Nama dan kategori wajib diisi.')->danger()->send();
            return;
        }

        // Cari garment_type_id dari nama jenis (flexible match)
        $garmentTypeId = null;
        if ($jenisNama) {
            $gt = GarmentType::active()
                ->where('garment_category_id', $kategoriId)
                ->where(function ($q) use ($jenisNama) {
                    $q->where('contoh_pakaian', $jenisNama)
                      ->orWhere('sub_kategori', $jenisNama)
                      ->orWhereRaw('LOWER(contoh_pakaian) LIKE ?', ['%' . strtolower($jenisNama) . '%']);
                })
                ->first();
            $garmentTypeId = $gt?->id;
        }

        // Bangun size_distribution dari ukuran yang user isi manual (lookup ID dari nama)
        $sizeDistribution = [];
        $totalFromSizes   = 0;
        if (!empty($ukuranData)) {
            $sizeCache = ProductSize::pluck('id', 'nama')->toArray();
            foreach ($ukuranData as $row) {
                $sizeName = trim($row['nama'] ?? '');
                $qty      = (int) ($row['jumlah'] ?? 0);
                if (!$sizeName || $qty < 1) continue;
                $sizeId             = $sizeCache[$sizeName] ?? null;
                $sizeDistribution[] = ['product_size_id' => $sizeId, 'jumlah' => $qty];
                $totalFromSizes += $qty;
            }
        }
        $jumlah = (int) ($data['jumlah'] ?? 0);
        if ($jumlah < 1 && $totalFromSizes > 0) $jumlah = $totalFromSizes;

        $product = Product::create([
            'product_code'        => Product::generateCode($kategoriId),
            'garment_category_id' => $kategoriId,
            'garment_type_id'     => $garmentTypeId,
            'nama'                => $nama,
            'deskripsi'           => $deskripsi,
            'jumlah'              => $jumlah,
            'harga'               => $harga,
            'status'              => 'aktif',
            'video_url'           => $videoUrl ?: null,
            'warehouse_id'        => $warehouseId,
            'created_by'          => Auth::id(),
        ]);

        $product->syncUnits($jumlah, $warehouseId, $sizeDistribution);

        // Simpan foto yang sudah diupload
        $photos = array_values(array_filter($data['photos'] ?? []));
        foreach ($photos as $idx => $path) {
            if (!Storage::disk('public')->exists($path)) continue;
            ProductPhoto::create([
                'product_id' => $product->id,
                'photo_path' => $path,
                'urutan'     => $idx,
            ]);
        }

        // Simpan warna dari AI
        if (!empty($warna)) {
            foreach ($warna as $i => $w) {
                $hex = strtoupper(trim($w['hex'] ?? ''));
                if (!preg_match('/^#[0-9A-F]{6}$/', $hex)) continue;
                ProductColor::create([
                    'product_id' => $product->id,
                    'hex'        => $hex,
                    'nama'       => $w['nama'] ?? null,
                    'urutan'     => $i,
                ]);
            }
        }

        Notification::make()
            ->title("Produk \"{$nama}\" berhasil disimpan!")
            ->body('Silakan edit untuk menambah foto dan melengkapi detail lainnya.')
            ->success()
            ->send();

        $this->redirect($this->getResource()::getUrl('edit', ['record' => $product->id]));
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.resources.products.pages.create-product-header');
    }
}
