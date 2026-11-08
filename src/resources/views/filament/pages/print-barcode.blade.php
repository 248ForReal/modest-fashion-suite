<x-filament-panels::page>
@php
    $pid     = $cfg['product_id'] ?? null;
    $jumlah  = (int) ($cfg['jumlah'] ?? 1);
    $kertas  = $cfg['kertas'] ?? 'A4';
    $kolom   = max(1, (int) ($cfg['kolom'] ?? 3));
    $baris   = max(1, (int) ($cfg['baris'] ?? 8));
    $lebar   = max(20, (int) ($cfg['lebar'] ?? 60));
    $tinggi  = max(10, (int) ($cfg['tinggi'] ?? 30));
    $stok    = $this->getStokCount();
    $maxJml  = $pid ? $stok : 200;
    $perPage = $kolom * $baris;
    // Preset stiker: format WxH → size CSS langsung dalam mm
    $stickerPresets = [
        '100x150' => ['w' => 100, 'h' => 150, 'kolom' => 1, 'baris' => 1],
        '60x40'   => ['w' => 60,  'h' => 40,  'kolom' => 1, 'baris' => 1],
        '50x30'   => ['w' => 50,  'h' => 30,  'kolom' => 1, 'baris' => 1],
        '80x50'   => ['w' => 80,  'h' => 50,  'kolom' => 1, 'baris' => 1],
        '100x50'  => ['w' => 100, 'h' => 50,  'kolom' => 1, 'baris' => 1],
    ];
    $isStiker = isset($stickerPresets[$kertas]);
    if ($isStiker) {
        $sw = $stickerPresets[$kertas]['w'];
        $sh = $stickerPresets[$kertas]['h'];
        $paperCss = "size:{$sw}mm {$sh}mm;";
        // Saat stiker: label = 1 per lembar, ukuran = dimensi kertas dikurangi margin
        $lebar  = $sw - 4;
        $tinggi = $sh - 8;
        $kolom  = 1;
        $baris  = 1;
    } else {
        $paperCss = match($kertas) { 'A5' => 'size:A5;', 'Letter' => 'size:Letter;', default => 'size:A4;' };
    }
@endphp
<div>

{{-- SETTING + PREVIEW --}}
<div style="display:grid; grid-template-columns:320px 1fr; gap:20px; align-items:start;" class="no-print">

    {{-- PANEL KIRI --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06);">
        <p style="font-weight:600; font-size:15px; color:#111827; margin:0 0 16px;">Pengaturan Cetak</p>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Produk</label>
            <select wire:model.live="cfg.product_id" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; color:#111827; background:#fff;">
                <option value="">-- Pilih Produk --</option>
                @foreach($this->getProductOptions() as $id => $label)
                    <option value="{{ $id }}" @selected($pid == $id)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if($pid)
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:8px 12px; font-size:12px; color:#1d4ed8; margin-bottom:14px;">
            Barcode per unit — unik & bisa di-scan · Unit status <strong>stok</strong>: <strong>{{ $stok }}</strong> unit tersedia
        </div>
        @endif

        <div style="margin-bottom:14px;">
            <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">
                Jumlah Label <span style="color:#9ca3af;">(maks {{ $maxJml }})</span>
            </label>
            <input type="number" wire:model.live="cfg.jumlah" min="1" max="{{ $maxJml }}"
                style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; box-sizing:border-box;" />
        </div>

        <hr style="border:none; border-top:1px solid #f3f4f6; margin:14px 0;" />
        <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin:0 0 10px;">Layout Kertas</p>

        <div style="margin-bottom:12px;">
            <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Ukuran Kertas</label>
            <select wire:model.live="cfg.kertas" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff;">
                @foreach($this->getPaperSizes() as $val => $lbl)
                    <option value="{{ $val }}" @selected($kertas===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        @if($isStiker)
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:8px 12px; font-size:12px; color:#15803d; margin-bottom:12px;">
            Mode stiker — 1 label per lembar &bull; Label {{ $lebar }}×{{ $tinggi }}mm (otomatis)
        </div>
        @else
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Kolom</label>
                <input type="number" wire:model.live="cfg.kolom" min="1" max="10" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; box-sizing:border-box;" />
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Baris</label>
                <input type="number" wire:model.live="cfg.baris" min="1" max="20" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; box-sizing:border-box;" />
            </div>
        </div>

        <hr style="border:none; border-top:1px solid #f3f4f6; margin:14px 0;" />
        <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin:0 0 10px;">Ukuran Label (mm)</p>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Lebar</label>
                <input type="number" wire:model.live="cfg.lebar" min="20" max="200" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; box-sizing:border-box;" />
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Tinggi</label>
                <input type="number" wire:model.live="cfg.tinggi" min="10" max="150" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; box-sizing:border-box;" />
            </div>
        </div>
        @endif

        @if($pid)
        <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:8px 12px; font-size:12px; color:#c2410c; margin-bottom:14px;">
            {{ $jumlah }} label &bull; {{ $kolom }}×{{ $baris }} per halaman &bull; {{ max(1, ceil($jumlah / $perPage)) }} halaman
        </div>
        @endif

        <button onclick="window.print()" @if(!$pid) disabled @endif
            style="width:100%; background:{{ $pid ? '#ea580c' : '#d1d5db' }}; color:#fff; border:none; border-radius:8px; padding:10px; font-size:13px; font-weight:600; cursor:{{ $pid ? 'pointer' : 'not-allowed' }};">
            Cetak Barcode
        </button>
    </div>

    {{-- PANEL KANAN — PREVIEW --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06);">
        <p style="font-weight:600; font-size:15px; color:#111827; margin:0 0 16px;">Preview</p>

        @if(!$pid)
        <div style="height:180px; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:13px;">
            Pilih produk untuk melihat preview barcode
        </div>
        @else
            @php
                $barcodes   = $this->getBarcodesData();
                $previewMax = min(count($barcodes), $kolom * min($baris, 3));
                $previewItems = array_slice($barcodes, 0, $previewMax);
                // Lebar total grid preview dalam mm → scale ke max 560px container
                $gridWidthMm = $kolom * $lebar + ($kolom - 1) * 2;
                $scaleFactor = min(1, 560 / ($gridWidthMm * 3.78)); // 1mm ≈ 3.78px
            @endphp
            @if(count($barcodes) === 0)
            <div style="height:180px; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:13px;">
                Tidak ada unit stok untuk produk ini
            </div>
            @else
            {{-- Preview di-scale agar muat di layar --}}
            <div style="overflow:hidden; width:100%;">
                <div style="transform-origin:top left; transform:scale({{ number_format($scaleFactor, 3) }}); width:{{ round(100 / $scaleFactor) }}%;">
                    <div style="display:grid; grid-template-columns:repeat({{ $kolom }}, {{ $lebar }}mm); gap:2mm;">
                        @foreach($previewItems as $item)
                        <div style="width:{{ $lebar }}mm; height:{{ $tinggi+8 }}mm; border:0.5pt dashed #d1d5db; padding:0.5mm 1mm; box-sizing:border-box; text-align:center; overflow:hidden; background:#fff; display:flex; flex-direction:column; justify-content:space-between;">
                            <div style="flex:1; min-height:0; overflow:hidden;">{!! $item['svg'] !!}</div>
                            <div style="flex-shrink:0;">
                                <div style="font-size:7pt; font-weight:700; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['code'] }}</div>
                                <div style="font-size:6pt; color:#6b7280; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['nama'] }}@if(!empty($item['ukuran'])) · {{ $item['ukuran'] }}@endif</div>
                                <div style="font-size:7pt; font-weight:700; color:#ea580c; line-height:1.3;">{{ $item['harga'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <p style="font-size:11px; color:#9ca3af; margin-top:{{ round(($tinggi + 10) * min($baris,3) * 3.78 * $scaleFactor + 8) }}px 0 0;">
                    Preview {{ $previewMax }} dari {{ count($barcodes) }} label
                    @if(count($barcodes) > $previewMax) · <em>sisanya tercetak semua</em>@endif
                </p>
            </div>
            @endif
        @endif
    </div>
</div>

{{-- PRINT AREA --}}
@if($pid)
@php $pages = array_chunk($this->getBarcodesData(), $perPage); @endphp
<div class="print-area">
    @foreach($pages as $page)
    <div>
        <div style="display:grid; grid-template-columns:repeat({{ $kolom }}, {{ $lebar }}mm); gap:2mm; justify-content:start;">
            @foreach($page as $item)
            <div style="width:{{ $lebar }}mm; height:{{ $tinggi+8 }}mm; border:0.5pt solid #ccc; padding:0.5mm 1mm; box-sizing:border-box; overflow:hidden; text-align:center; display:flex; flex-direction:column; justify-content:space-between;">
                <div style="flex:1; min-height:0; overflow:hidden;">{!! $item['svg'] !!}</div>
                <div style="flex-shrink:0;">
                    <div style="font-size:7pt; font-weight:700; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['code'] }}</div>
                    <div style="font-size:6pt; color:#555; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['nama'] }}@if(!empty($item['ukuran'])) · {{ $item['ukuran'] }}@endif</div>
                    <div style="font-size:7pt; font-weight:700; color:#ea580c; line-height:1.3;">{{ $item['harga'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @if(!$loop->last)<div style="page-break-after:always;"></div>@endif
    @endforeach
</div>
@endif

</div>

<style>
@media print {
    .no-print { display:none !important; }
    .print-area { display:block !important; }
    .fi-sidebar,.fi-topbar,.fi-header,.fi-breadcrumbs { display:none !important; }
    body { background:white !important; }
    @page { {{ $paperCss }} margin:8mm; }
}
@media screen { .print-area { display:none; } }
</style>
</x-filament-panels::page>
