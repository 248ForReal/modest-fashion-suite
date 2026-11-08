<?php

namespace App\Filament\Pages;

use App\Models\DistribusiOrder;
use App\Models\DistribusiOrderItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Warehouse;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class Distribusi extends Page
{
    use WithFileUploads;
    protected string $view = 'filament.pages.distribusi';
    protected static ?string $navigationLabel = 'Distribusi';
    protected static ?string $title = 'Distribusi Produk';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 5;

    public array $cfg = [
        'gudang_asal_id'   => null,
        'gudang_tujuan_id' => null,
        'jumlah'           => [],      // product_id => jumlah
        'posisi'           => [],      // product_id => posisi_tujuan
        'catatan'          => '',
        'confirmed'        => false,
        'order_id'         => null,    // setelah proses, simpan id order untuk upload foto
    ];

    // Terpisah dari cfg agar WithFileUploads bisa handle upload
    public $foto_bukti_file = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getGudangOptions(): array
    {
        return Warehouse::active()->orderBy('nama')->pluck('nama', 'id')->toArray();
    }

    public function getProdukDiGudang(): array
    {
        $gudangAsalId = $this->cfg['gudang_asal_id'] ? (int) $this->cfg['gudang_asal_id'] : null;
        if (!$gudangAsalId) return [];

        $rows = ProductUnit::with(['product.garmentCategory', 'size'])
            ->where('warehouse_id', $gudangAsalId)
            ->where('status', 'stok')
            ->get()
            ->groupBy('product_id');

        $result = [];
        foreach ($rows as $productId => $units) {
            $produk = $units->first()->product;
            if (!$produk) continue;

            // Hitung sebaran ukuran: ['S' => 3, 'M' => 5, ...]
            $ukuranMap = [];
            foreach ($units as $u) {
                $nama = $u->size?->nama;
                if ($nama) $ukuranMap[$nama] = ($ukuranMap[$nama] ?? 0) + 1;
            }

            $result[] = [
                'product_id'   => $productId,
                'product_code' => $produk->product_code,
                'nama'         => $produk->nama,
                'kategori'     => $produk->garmentCategory?->nama ?? '-',
                'stok_di_asal' => $units->count(),
                'ukuran_stok'  => $ukuranMap,   // ['S'=>3, 'M'=>5, ...]
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['nama'], $b['nama']));
        return $result;
    }

    public function getPreview(): array
    {
        $gudangAsalId   = $this->cfg['gudang_asal_id'] ? (int) $this->cfg['gudang_asal_id'] : null;
        $gudangTujuanId = $this->cfg['gudang_tujuan_id'] ? (int) $this->cfg['gudang_tujuan_id'] : null;
        $jumlahMap      = $this->cfg['jumlah'];

        if (!$gudangAsalId || !$gudangTujuanId) return [];

        $preview = [];
        foreach ($jumlahMap as $productId => $jumlah) {
            $jumlah = (int) $jumlah;
            if ($jumlah <= 0) continue;

            $stok   = ProductUnit::where('product_id', $productId)
                ->where('warehouse_id', $gudangAsalId)
                ->where('status', 'stok')
                ->count();
            $produk = Product::find($productId);
            if (!$produk) continue;

            $preview[] = [
                'product_id'    => $productId,
                'nama'          => $produk->nama,
                'product_code'  => $produk->product_code,
                'jumlah'        => min($jumlah, $stok),
                'stok'          => $stok,
                'cukup'         => $jumlah <= $stok,
                'posisi_tujuan' => $this->cfg['posisi'][$productId] ?? '',
            ];
        }

        return $preview;
    }

    public function prosesDistribusi(): void
    {
        $gudangAsalId   = $this->cfg['gudang_asal_id'] ? (int) $this->cfg['gudang_asal_id'] : null;
        $gudangTujuanId = $this->cfg['gudang_tujuan_id'] ? (int) $this->cfg['gudang_tujuan_id'] : null;
        $jumlahMap      = $this->cfg['jumlah'];
        $posisiMap      = $this->cfg['posisi'];

        if (!$gudangAsalId || !$gudangTujuanId) {
            Notification::make()->title('Pilih gudang asal dan tujuan')->warning()->send();
            return;
        }
        if ($gudangAsalId === $gudangTujuanId) {
            Notification::make()->title('Gudang asal dan tujuan tidak boleh sama')->danger()->send();
            return;
        }

        $totalDipindah = 0;
        $produkGagal   = [];

        // Buat distribusi order header
        $order = DistribusiOrder::create([
            'nomor'              => DistribusiOrder::generateNomor(),
            'warehouse_asal_id'  => $gudangAsalId,
            'warehouse_tujuan_id'=> $gudangTujuanId,
            'total_unit'         => 0,  // update setelah loop
            'catatan'            => $this->cfg['catatan'] ?: null,
            'status'             => 'selesai',
            'created_by'         => auth()->id(),
        ]);

        foreach ($jumlahMap as $productId => $jumlah) {
            $jumlah = (int) $jumlah;
            if ($jumlah <= 0) continue;

            $posisiTujuan = $posisiMap[$productId] ?? null;

            $units = ProductUnit::where('product_id', $productId)
                ->where('warehouse_id', $gudangAsalId)
                ->where('status', 'stok')
                ->orderBy('unit_code')
                ->limit($jumlah)
                ->get();

            if ($units->isEmpty()) continue;

            if ($units->count() < $jumlah) {
                $produk = Product::find($productId);
                $produkGagal[] = ($produk->nama ?? $productId) . " (minta {$jumlah}, tersedia {$units->count()})";
            }

            $dipindah = 0;
            foreach ($units as $unit) {
                $unit->update([
                    'warehouse_id' => $gudangTujuanId,
                    'posisi'       => $posisiTujuan ?: null,   // reset posisi jika tidak diisi
                ]);
                $dipindah++;
                $totalDipindah++;
            }

            if ($dipindah > 0) {
                InventoryTransaction::catatDistribusi(
                    $productId, $gudangAsalId, $gudangTujuanId, $dipindah
                );

                DistribusiOrderItem::create([
                    'distribusi_order_id' => $order->id,
                    'product_id'          => $productId,
                    'jumlah'              => $dipindah,
                    'posisi_tujuan'       => $posisiTujuan ?: null,
                ]);
            }
        }

        // Update total unit di order
        $order->update(['total_unit' => $totalDipindah]);

        $tujuan = Warehouse::find($gudangTujuanId);

        if ($totalDipindah > 0) {
            Notification::make()
                ->title("{$totalDipindah} unit berhasil dipindahkan ke {$tujuan->nama}")
                ->success()
                ->send();
        }

        if (!empty($produkGagal)) {
            Notification::make()
                ->title('Beberapa produk stok kurang: ' . implode(', ', $produkGagal))
                ->warning()->persistent()->send();
        }

        // Simpan order_id untuk upload foto, reset form distribusi
        $this->cfg['order_id']        = $order->id;
        $this->cfg['gudang_asal_id']  = null;
        $this->cfg['gudang_tujuan_id']= null;
        $this->cfg['jumlah']          = [];
        $this->cfg['posisi']          = [];
        $this->cfg['catatan']         = '';
        $this->cfg['confirmed']       = false;
    }

    public function uploadFotoBukti(): void
    {
        if (!$this->cfg['order_id'] || !$this->foto_bukti_file) return;

        $this->validate(['foto_bukti_file' => 'image|max:5120']);

        $path = $this->foto_bukti_file->store('distribusi-bukti', 'public');

        DistribusiOrder::where('id', $this->cfg['order_id'])
            ->update(['foto_bukti' => $path]);

        Notification::make()->title('Foto bukti berhasil disimpan')->success()->send();
        $this->cfg['order_id']  = null;
        $this->foto_bukti_file  = null;
    }

    public function skipFoto(): void
    {
        $this->cfg['order_id'] = null;
        $this->foto_bukti_file = null;
    }

    public function resetForm(): void
    {
        $this->cfg = [
            'gudang_asal_id'   => null,
            'gudang_tujuan_id' => null,
            'jumlah'           => [],
            'posisi'           => [],
            'catatan'          => '',
            'confirmed'        => false,
            'order_id'         => null,
        ];
        $this->foto_bukti_file = null;
    }
}
