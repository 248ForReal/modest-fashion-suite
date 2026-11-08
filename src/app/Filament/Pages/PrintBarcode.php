<?php

namespace App\Filament\Pages;

use App\Filament\Pages\WorkSettings;
use App\Models\Product;
use App\Models\ProductUnit;
use Filament\Pages\Page;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PrintBarcode extends Page
{
    protected string $view = 'filament.pages.print-barcode';
    protected static ?string $navigationLabel = 'Cetak Barcode';
    protected static ?string $title = 'Cetak Barcode Produk';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 3;

    // Semua state disimpan dalam satu array agar tidak bentrok dengan Filament property resolver
    public array $cfg = [
        'product_id' => null,
        'jumlah'     => 1,
        'kertas'     => 'A4',
        'kolom'      => 3,
        'baris'      => 8,
        'lebar'      => 60,
        'tinggi'     => 20,
    ];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->hasAnyRole(['super_admin', 'admin'])) return true;
        $role = $user->roles->first()?->name;
        if (!$role) return false;
        return WorkSettings::canAccessMenu($role, 'cetak_barcode');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getProductOptions(): array
    {
        return Product::orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($p) => [
                $p->id => "[{$p->product_code}] {$p->nama} (stok: {$p->units()->where('status','stok')->count()})"
            ])
            ->toArray();
    }

    public function getBarcodesData(): array
    {
        $pid = $this->cfg['product_id'] ?? null;
        if (!$pid) return [];

        $product = Product::find($pid);
        if (!$product) return [];

        $generator = new BarcodeGeneratorSVG();
        $harga     = 'Rp ' . number_format($product->harga, 0, ',', '.');
        $jumlah    = (int) ($this->cfg['jumlah'] ?? 1);

        $units = ProductUnit::with('size')
            ->where('product_id', $pid)
            ->where('status', 'stok')
            ->orderBy('unit_code')
            ->take($jumlah)
            ->get();

        return $units->map(function ($u) use ($generator, $product, $harga) {
            // Kode pendek: ambil 4 huruf random + nomor urut (strip tanggal 8 digit + 3 huruf kategori)
            $scanCode = preg_match('/^\d{8}[A-Z]{3}(.+)$/', $u->unit_code, $m) ? $m[1] : $u->unit_code;

            $svg = $generator->getBarcode($scanCode, $generator::TYPE_CODE_128);
            $svg = preg_replace('/<svg([^>]*) width="[^"]*"/', '<svg$1', $svg);
            $svg = preg_replace('/<svg([^>]*) height="[^"]*"/', '<svg$1', $svg);
            $svg = preg_replace('/<svg/', '<svg style="width:100%;height:100%;display:block;"', $svg, 1);

            return [
                'svg'   => $svg,
                'code'  => $scanCode,
                'nama'  => $product->nama,
                'harga' => $harga,
                'ukuran'=> $u->size?->nama ?? null,
            ];
        })->toArray();
    }

    public function getStokCount(): int
    {
        $pid = $this->cfg['product_id'] ?? null;
        if (!$pid) return 0;
        return ProductUnit::where('product_id', $pid)->where('status', 'stok')->count();
    }

    public function getPaperSizes(): array
    {
        return [
            'A4'       => 'A4 (210×297mm) — kertas biasa',
            'A5'       => 'A5 (148×210mm) — kertas biasa',
            'Letter'   => 'Letter (216×279mm) — kertas biasa',
            '100x150'  => 'Stiker 100×150mm — resi pengiriman',
            '60x40'    => 'Stiker 60×40mm — label baju (rekomendasi)',
            '50x30'    => 'Stiker 50×30mm — label kecil',
            '80x50'    => 'Stiker 80×50mm — label sedang',
            '100x50'   => 'Stiker 100×50mm — label panjang',
        ];
    }
}
