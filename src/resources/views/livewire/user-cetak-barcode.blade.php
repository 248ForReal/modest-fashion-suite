@php
    $pid    = $cfg['product_id'] ?? null;
    $jumlah = (int) ($cfg['jumlah'] ?? 1);
    $kolom  = max(1, (int) ($cfg['kolom'] ?? 2));
    $lebar  = max(20, (int) ($cfg['lebar'] ?? 60));
    $tinggi = max(10, (int) ($cfg['tinggi'] ?? 30));
    $stok   = $this->getStokCount();
@endphp

<div style="max-width:640px; margin:0 auto;">

    {{-- Setting --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:16px;" class="no-print">

        <p style="font-weight:600; font-size:15px; color:#111827; margin:0 0 16px;">Pengaturan Cetak</p>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Produk</label>
            <select wire:model.live="cfg.product_id" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; color:#111827; background:#fff; box-sizing:border-box;">
                <option value="">-- Pilih Produk --</option>
                @foreach($this->getProductOptions() as $id => $label)
                    <option value="{{ $id }}" @selected($pid == $id)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if($pid)
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:8px 12px; font-size:12px; color:#1d4ed8; margin-bottom:14px;">
            Unit status <strong>stok tersedia: {{ $stok }} unit</strong>
        </div>
        @endif

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Jumlah Label</label>
                <input type="number" wire:model.live="cfg.jumlah" min="1" max="{{ $pid ? $stok : 200 }}"
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; box-sizing:border-box;" />
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Kolom per Baris</label>
                <input type="number" wire:model.live="cfg.kolom" min="1" max="4"
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; box-sizing:border-box;" />
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Lebar Label (mm)</label>
                <input type="number" wire:model.live="cfg.lebar" min="20" max="200"
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; box-sizing:border-box;" />
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Tinggi Label (mm)</label>
                <input type="number" wire:model.live="cfg.tinggi" min="10" max="150"
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; box-sizing:border-box;" />
            </div>
        </div>

        @if($pid)
        <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:8px 12px; font-size:12px; color:#c2410c; margin-bottom:14px;">
            {{ $jumlah }} label akan dicetak · {{ $kolom }} kolom per baris
        </div>
        @endif

        <button onclick="window.print()" @if(!$pid) disabled @endif
            style="width:100%; background:{{ $pid ? '#ea580c' : '#d1d5db' }}; color:#fff; border:none; border-radius:8px; padding:12px; font-size:14px; font-weight:600; cursor:{{ $pid ? 'pointer' : 'not-allowed' }};">
            Cetak Barcode
        </button>
    </div>

    {{-- Preview (screen only) --}}
    @if($pid)
    @php $barcodes = $this->getBarcodesData(); @endphp
    @if(count($barcodes) > 0)
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; margin-bottom:16px;" class="no-print">
        <p style="font-size:13px; font-weight:600; color:#374151; margin:0 0 12px;">Preview ({{ min(count($barcodes), $kolom * 2) }} dari {{ count($barcodes) }} label)</p>
        <div style="overflow-x:auto;">
            <div style="display:grid; grid-template-columns:repeat({{ $kolom }}, {{ $lebar }}px); gap:4px; width:fit-content;">
                @foreach(array_slice($barcodes, 0, $kolom * 2) as $item)
                <div style="border:1px dashed #d1d5db; padding:4px; text-align:center; width:{{ $lebar }}px; height:{{ $tinggi + 24 }}px; overflow:hidden; box-sizing:border-box;">
                    <div style="width:100%; height:{{ $tinggi - 6 }}px; overflow:hidden;">{!! $item['svg'] !!}</div>
                    <div style="font-size:6px; font-weight:600; line-height:1.2; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">{{ $item['code'] }}</div>
                    <div style="font-size:5px; color:#6b7280; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['nama'] }}</div>
                    <div style="font-size:6px; font-weight:700; color:#ea580c; line-height:1.2;">{{ $item['harga'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif

</div>

{{-- Print Area --}}
@php $paperCss = 'size:A4;'; @endphp
@if($pid)
@php
    $barcodesPrint = $this->getBarcodesData();
    $pages = array_chunk($barcodesPrint, $kolom * 8);
@endphp
<div class="print-area">
    @foreach($pages as $page)
    <div>
        <div style="display:grid; grid-template-columns:repeat({{ $kolom }}, {{ $lebar }}mm); gap:2mm; justify-content:start;">
            @foreach($page as $item)
            <div style="width:{{ $lebar }}mm; height:{{ $tinggi + 10 }}mm; border:0.5pt solid #ccc; padding:1mm; box-sizing:border-box; overflow:hidden; text-align:center;">
                <div style="width:100%; height:{{ $tinggi - 4 }}mm; overflow:hidden;">{!! $item['svg'] !!}</div>
                <div style="font-size:6pt; font-weight:700; margin-top:.5mm; line-height:1.1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['code'] }}</div>
                <div style="font-size:5pt; color:#555; line-height:1.1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['nama'] }}</div>
                <div style="font-size:6pt; font-weight:700; color:#ea580c; line-height:1.2;">{{ $item['harga'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @if(!$loop->last)<div style="page-break-after:always;"></div>@endif
    @endforeach
</div>
@endif

<style>
@media print {
    .no-print { display:none !important; }
    .print-area { display:block !important; }
    nav, header { display:none !important; }
    body { background:white !important; }
    @page { {{ $paperCss }} margin:8mm; }
}
@media screen { .print-area { display:none; } }
</style>
