<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
use Livewire\Component;
use Picqer\Barcode\BarcodeGeneratorSVG;

class UserCetakBarcode extends Component
{
    public array $cfg = [
        'product_id' => null,
        'jumlah'     => 1,
        'kolom'      => 2,
        'lebar'      => 60,
        'tinggi'     => 30,
    ];

    public function getProductOptions(): array
    {
        return Product::orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($p) => [
                $p->id => "[{$p->product_code}] {$p->nama} (stok: {$p->units()->where('status','stok')->count()})"
            ])
            ->toArray();
    }

    public function getStokCount(): int
    {
        $pid = $this->cfg['product_id'] ?? null;
        if (!$pid) return 0;
        return ProductUnit::where('product_id', $pid)->where('status', 'stok')->count();
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

        $units = ProductUnit::where('product_id', $pid)
            ->where('status', 'stok')
            ->orderBy('unit_code')
            ->take($jumlah)
            ->get();

        return $units->map(fn ($u) => [
            'svg'   => $generator->getBarcode($u->unit_code, $generator::TYPE_CODE_128),
            'code'  => $u->unit_code,
            'nama'  => $product->nama,
            'harga' => $harga,
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.user-cetak-barcode');
    }
}
