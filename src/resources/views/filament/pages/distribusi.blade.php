<x-filament-panels::page>
@php
    $gudangOpts     = $this->getGudangOptions();
    $gudangAsalId   = $cfg['gudang_asal_id']   ?? null;
    $gudangTujuanId = $cfg['gudang_tujuan_id'] ?? null;
    $jumlahMap      = $cfg['jumlah']           ?? [];
    $posisiMap      = $cfg['posisi']           ?? [];
    $orderId        = $cfg['order_id']         ?? null;
    $hasFotoFile    = !empty($foto_bukti_file);
    $produkList     = $this->getProdukDiGudang();
    $preview        = $this->getPreview();

    $totalDipilih = array_sum(array_map('intval', $jumlahMap));

    $gudangAsal   = $gudangAsalId   ? ($gudangOpts[(int)$gudangAsalId]   ?? '-') : null;
    $gudangTujuan = $gudangTujuanId ? ($gudangOpts[(int)$gudangTujuanId] ?? '-') : null;

    $step1Done = $gudangAsalId && $gudangTujuanId;
    $step2Done = $step1Done && $totalDipilih > 0;
@endphp

{{-- ───── PANEL UPLOAD FOTO (muncul setelah proses berhasil) ───── --}}
@if($orderId)
<div style="background:#fff; border:2px solid #16a34a; border-radius:14px; padding:24px; margin-bottom:20px; box-shadow:0 4px 16px rgba(22,163,74,.1);">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
        <div style="width:32px; height:32px; border-radius:50%; background:#dcfce7; display:flex; align-items:center; justify-content:center;">
            <svg style="width:18px;height:18px;color:#16a34a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <p style="font-size:14px; font-weight:700; color:#15803d; margin:0;">Distribusi Berhasil!</p>
            <p style="font-size:12px; color:#6b7280; margin:0;">Tambahkan foto bukti pengiriman (opsional)</p>
        </div>
    </div>

    <div style="border:2px dashed #86efac; border-radius:10px; padding:20px; text-align:center; background:#f0fdf4; margin-bottom:14px;">
        <svg style="width:32px;height:32px;color:#86efac;margin:0 auto 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
        <p style="font-size:13px; color:#374151; margin:0 0 10px; font-weight:500;">Upload foto bukti pengiriman</p>
        <input type="file" wire:model="foto_bukti_file" accept="image/*"
            style="display:block; margin:0 auto; font-size:12px; color:#374151;" />
        <p style="font-size:11px; color:#9ca3af; margin:6px 0 0;">Format: JPG, PNG, WEBP · Maks 5MB</p>
    </div>

    <div style="display:flex; gap:10px;">
        <button wire:click="uploadFotoBukti"
            @if(!$hasFotoFile) disabled @endif
            style="flex:1; background:{{ $hasFotoFile ? '#16a34a' : '#d1d5db' }}; color:#fff; border:none; border-radius:8px; padding:10px; font-size:13px; font-weight:600; cursor:{{ $hasFotoFile ? 'pointer' : 'not-allowed' }};">
            Simpan Foto Bukti
        </button>
        <button wire:click="skipFoto" type="button"
            style="padding:10px 20px; background:#f3f4f6; color:#6b7280; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:500;">
            Lewati
        </button>
    </div>
</div>
@endif

{{-- Step indicator --}}
<div style="display:flex; align-items:center; gap:0; margin-bottom:24px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 20px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <div style="display:flex; align-items:center; gap:8px;">
        <div style="width:26px; height:26px; border-radius:50%; background:{{ $step1Done ? '#16a34a' : '#6366f1' }}; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;">
            @if($step1Done)✓@else 1 @endif
        </div>
        <span style="font-size:13px; font-weight:600; color:{{ $step1Done ? '#15803d' : '#111827' }};">Pilih Gudang</span>
    </div>
    <div style="flex:1; height:2px; background:{{ $step1Done ? '#86efac' : '#e5e7eb' }}; margin:0 12px;"></div>
    <div style="display:flex; align-items:center; gap:8px;">
        <div style="width:26px; height:26px; border-radius:50%; background:{{ $step2Done ? '#16a34a' : ($step1Done ? '#6366f1' : '#e5e7eb') }}; color:{{ $step1Done ? '#fff' : '#9ca3af' }}; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;">
            @if($step2Done)✓@else 2 @endif
        </div>
        <span style="font-size:13px; font-weight:600; color:{{ $step2Done ? '#15803d' : ($step1Done ? '#111827' : '#9ca3af') }};">Input Jumlah & Posisi</span>
    </div>
    <div style="flex:1; height:2px; background:{{ $step2Done ? '#86efac' : '#e5e7eb' }}; margin:0 12px;"></div>
    <div style="display:flex; align-items:center; gap:8px;">
        <div style="width:26px; height:26px; border-radius:50%; background:{{ $step2Done ? '#ea580c' : '#e5e7eb' }}; color:{{ $step2Done ? '#fff' : '#9ca3af' }}; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;">3</div>
        <span style="font-size:13px; font-weight:600; color:{{ $step2Done ? '#111827' : '#9ca3af' }};">Proses</span>
    </div>
</div>

{{-- STEP 1: Pilih gudang --}}
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; margin-bottom:16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <p style="font-size:13px; font-weight:700; color:#111827; margin:0 0 14px; text-transform:uppercase; letter-spacing:.04em;">1. Pilih Gudang Asal & Tujuan</p>
    <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:12px; align-items:end;">
        <div>
            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px;">Gudang Asal <span style="color:#ef4444;">*</span></label>
            <select wire:model.live="cfg.gudang_asal_id" style="width:100%; border:1.5px solid {{ $gudangAsalId ? '#86efac' : '#e5e7eb' }}; border-radius:8px; padding:9px 12px; font-size:14px; background:#fff; box-sizing:border-box;">
                <option value="">-- Pilih gudang asal --</option>
                @foreach($gudangOpts as $wid => $wnama)
                <option value="{{ $wid }}" @selected($gudangAsalId == $wid)>{{ $wnama }}</option>
                @endforeach
            </select>
            @if($gudangAsal)<p style="font-size:11px; color:#15803d; margin:4px 0 0; font-weight:500;">✓ {{ $gudangAsal }}</p>@endif
        </div>
        <div style="display:flex; flex-direction:column; align-items:center; padding-bottom:10px;">
            <svg style="width:24px; height:24px; color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
        <div>
            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px;">Gudang Tujuan <span style="color:#ef4444;">*</span></label>
            <select wire:model.live="cfg.gudang_tujuan_id" style="width:100%; border:1.5px solid {{ $gudangTujuanId ? '#86efac' : '#e5e7eb' }}; border-radius:8px; padding:9px 12px; font-size:14px; background:#fff; box-sizing:border-box;">
                <option value="">-- Pilih gudang tujuan --</option>
                @foreach($gudangOpts as $wid => $wnama)
                @if($wid != $gudangAsalId)
                <option value="{{ $wid }}" @selected($gudangTujuanId == $wid)>{{ $wnama }}</option>
                @endif
                @endforeach
            </select>
            @if($gudangTujuan)<p style="font-size:11px; color:#15803d; margin:4px 0 0; font-weight:500;">✓ {{ $gudangTujuan }}</p>@endif
        </div>
    </div>
    @if($gudangAsalId && $gudangTujuanId && $gudangAsalId == $gudangTujuanId)
    <div style="margin-top:10px; background:#fee2e2; border:1px solid #fca5a5; border-radius:8px; padding:8px 12px; font-size:12px; color:#b91c1c; font-weight:500;">
        Gudang asal dan tujuan tidak boleh sama.
    </div>
    @endif
</div>

{{-- STEP 2: Input jumlah + posisi per produk --}}
@if($gudangAsalId && $gudangTujuanId && $gudangAsalId != $gudangTujuanId)
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; margin-bottom:16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; background:#fafafa; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
        <div>
            <p style="font-size:13px; font-weight:700; color:#111827; margin:0; text-transform:uppercase; letter-spacing:.04em;">2. Produk, Jumlah & Posisi Tujuan</p>
            <p style="font-size:12px; color:#6b7280; margin:3px 0 0;">Stok di <strong>{{ $gudangAsal }}</strong> · Posisi tujuan = posisi unit di <strong>{{ $gudangTujuan }}</strong></p>
        </div>
        @if($totalDipilih > 0)
        <span style="background:#dbeafe; color:#1d4ed8; font-size:13px; font-weight:700; padding:5px 14px; border-radius:9999px;">{{ $totalDipilih }} unit akan dipindah</span>
        @endif
    </div>

    @if(count($produkList) === 0)
    <div style="padding:40px; text-align:center; color:#9ca3af; font-size:14px;">
        Tidak ada produk dengan stok di gudang <strong>{{ $gudangAsal }}</strong>.
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                    <th style="text-align:left; padding:10px 14px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.04em;">Produk</th>
                    <th style="text-align:center; padding:10px 10px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap;">Stok</th>
                    <th style="text-align:center; padding:10px 10px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap;">Jumlah Pindah</th>
                    <th style="text-align:left; padding:10px 14px; font-size:11px; font-weight:700; color:#059669; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap;">Posisi di Gudang Tujuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produkList as $p)
                @php
                    $jumlahIsi = (int) ($jumlahMap[$p['product_id']] ?? 0);
                    $posisiIsi = $posisiMap[$p['product_id']] ?? '';
                    $kurang    = $jumlahIsi > $p['stok_di_asal'];
                @endphp
                <tr style="border-bottom:1px solid #f3f4f6; {{ $jumlahIsi > 0 ? 'background:#f0f9ff;' : '' }}">
                    <td style="padding:12px 14px;">
                        <p style="font-size:13px; font-weight:600; color:#111827; margin:0;">{{ $p['nama'] }}</p>
                        <div style="display:flex; align-items:center; gap:6px; margin-top:2px; flex-wrap:wrap;">
                            <span style="font-size:10px; font-family:monospace; color:#6b7280; background:#f3f4f6; padding:1px 6px; border-radius:4px;">{{ $p['product_code'] }}</span>
                            @if($p['kategori'] !== '-')<span style="font-size:10px; color:#9ca3af;">{{ $p['kategori'] }}</span>@endif
                        </div>
                        @if(!empty($p['ukuran_stok']))
                        <div style="display:flex; flex-wrap:wrap; gap:3px; margin-top:4px;">
                            @foreach($p['ukuran_stok'] as $uk => $qty)
                            <span style="font-size:9px; font-weight:700; background:#ede9fe; color:#7c3aed; padding:1px 6px; border-radius:9999px;">{{ $uk }}: {{ $qty }}</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td style="text-align:center; padding:10px;">
                        <span style="font-size:15px; font-weight:700; color:#15803d;">{{ $p['stok_di_asal'] }}</span>
                        <p style="font-size:10px; color:#9ca3af; margin:1px 0 0;">unit</p>
                    </td>
                    <td style="text-align:center; padding:10px;">
                        <div style="display:flex; align-items:center; justify-content:center; gap:5px;">
                            <button wire:click="$set('cfg.jumlah.{{ $p['product_id'] }}', {{ max(0, $jumlahIsi - 1) }})" type="button"
                                style="width:26px; height:26px; border-radius:6px; border:1.5px solid #e5e7eb; background:#f9fafb; color:#374151; font-size:16px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1;">−</button>
                            <input wire:model.live="cfg.jumlah.{{ $p['product_id'] }}" type="number" min="0" max="{{ $p['stok_di_asal'] }}"
                                style="width:54px; text-align:center; border:1.5px solid {{ $kurang ? '#fca5a5' : ($jumlahIsi > 0 ? '#93c5fd' : '#e5e7eb') }}; border-radius:6px; padding:5px 4px; font-size:14px; font-weight:700; color:{{ $kurang ? '#b91c1c' : '#111827' }}; background:#fff;">
                            <button wire:click="$set('cfg.jumlah.{{ $p['product_id'] }}', {{ min($p['stok_di_asal'], $jumlahIsi + 1) }})" type="button"
                                style="width:26px; height:26px; border-radius:6px; border:1.5px solid #e5e7eb; background:#f9fafb; color:#374151; font-size:16px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1;">+</button>
                        </div>
                        @if($kurang)<p style="font-size:10px; color:#b91c1c; margin:3px 0 0; font-weight:500; text-align:center;">Melebihi stok!</p>@endif
                        @if($jumlahIsi === $p['stok_di_asal'] && $jumlahIsi > 0)<p style="font-size:10px; color:#d97706; margin:3px 0 0; text-align:center;">Semua stok</p>@endif
                    </td>
                    <td style="padding:10px 14px;">
                        @if($jumlahIsi > 0)
                        <input wire:model.live="cfg.posisi.{{ $p['product_id'] }}" type="text"
                            placeholder="cth: Rak A-3, Slot B2..."
                            style="width:100%; border:1.5px solid {{ $posisiIsi ? '#6ee7b7' : '#e5e7eb' }}; border-radius:6px; padding:6px 8px; font-size:12px; box-sizing:border-box; background:#fff; color:#111827;" />
                        @if($posisiIsi)
                        <p style="font-size:10px; color:#059669; margin:2px 0 0; font-weight:500;">→ {{ $posisiIsi }}</p>
                        @endif
                        @else
                        <span style="font-size:11px; color:#d1d5db;">— isi jumlah dulu</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Quick actions --}}
    <div style="padding:12px 16px; background:#f9fafb; border-top:1px solid #f3f4f6; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
        <span style="font-size:12px; color:#6b7280;">Pilih cepat:</span>
        <button wire:click="$set('cfg.jumlah', {{ json_encode(array_column($produkList, 'stok_di_asal', 'product_id')) }})" type="button"
            style="font-size:11px; color:#15803d; background:#dcfce7; border:1px solid #86efac; border-radius:5px; padding:3px 10px; cursor:pointer; font-weight:600;">Semua Stok</button>
        <button wire:click="$set('cfg.jumlah', [])" type="button"
            style="font-size:11px; color:#6b7280; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:5px; padding:3px 10px; cursor:pointer; font-weight:500;">Reset Jumlah</button>
        <button wire:click="$set('cfg.posisi', [])" type="button"
            style="font-size:11px; color:#6b7280; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:5px; padding:3px 10px; cursor:pointer; font-weight:500;">Reset Posisi</button>
    </div>
    @endif
</div>

{{-- STEP 3: Preview & Proses --}}
@if($totalDipilih > 0)
<div style="background:#fff; border:2px solid #ea580c; border-radius:12px; padding:20px; box-shadow:0 4px 16px rgba(234,88,12,.1); margin-bottom:16px;">
    <p style="font-size:13px; font-weight:700; color:#111827; margin:0 0 14px; text-transform:uppercase; letter-spacing:.04em;">3. Konfirmasi Distribusi</p>

    {{-- Summary --}}
    <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:14px 18px; margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div style="text-align:center; flex:1; min-width:100px;">
                <p style="font-size:11px; color:#92400e; margin:0; font-weight:600; text-transform:uppercase;">Dari</p>
                <p style="font-size:16px; font-weight:700; color:#111827; margin:4px 0 0;">{{ $gudangAsal }}</p>
            </div>
            <svg style="width:28px; height:28px; color:#ea580c; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <div style="text-align:center; flex:1; min-width:100px;">
                <p style="font-size:11px; color:#92400e; margin:0; font-weight:600; text-transform:uppercase;">Ke</p>
                <p style="font-size:16px; font-weight:700; color:#111827; margin:4px 0 0;">{{ $gudangTujuan }}</p>
            </div>
            <div style="text-align:center; flex:1; min-width:80px; border-left:1px solid #fed7aa; padding-left:12px;">
                <p style="font-size:11px; color:#92400e; margin:0; font-weight:600; text-transform:uppercase;">Total</p>
                <p style="font-size:24px; font-weight:800; color:#ea580c; margin:2px 0 0; line-height:1;">{{ $totalDipilih }}</p>
                <p style="font-size:10px; color:#92400e; margin:0;">unit</p>
            </div>
        </div>
    </div>

    {{-- List produk yang akan dipindah --}}
    <div style="margin-bottom:14px; border:1px solid #f3f4f6; border-radius:8px; overflow:hidden;">
        <div style="padding:8px 12px; background:#f9fafb; border-bottom:1px solid #f3f4f6;">
            <p style="font-size:11px; font-weight:700; color:#6b7280; margin:0; text-transform:uppercase; letter-spacing:.04em;">Detail Produk</p>
        </div>
        @foreach($preview as $item)
        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-bottom:1px solid #f3f4f6; gap:8px; flex-wrap:wrap; {{ !$item['cukup'] ? 'background:#fff7ed;' : '' }}">
            <div style="flex:1; min-width:120px;">
                <p style="font-size:13px; font-weight:600; color:#111827; margin:0;">{{ $item['nama'] }}</p>
                <p style="font-size:10px; font-family:monospace; color:#9ca3af; margin:1px 0 0;">{{ $item['product_code'] }}</p>
            </div>
            @if($item['posisi_tujuan'])
            <span style="font-size:11px; color:#059669; background:#d1fae5; padding:2px 8px; border-radius:5px; font-weight:600; white-space:nowrap;">
                Posisi: {{ $item['posisi_tujuan'] }}
            </span>
            @endif
            <div style="text-align:right; white-space:nowrap;">
                <span style="font-size:14px; font-weight:700; color:{{ $item['cukup'] ? '#1d4ed8' : '#b91c1c' }};">{{ $item['jumlah'] }} unit</span>
                @if(!$item['cukup'])
                <p style="font-size:10px; color:#b91c1c; margin:1px 0 0; font-weight:500;">Stok hanya {{ $item['stok'] }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Catatan order --}}
    <div style="margin-bottom:14px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px;">Catatan <span style="color:#9ca3af; font-weight:400;">(opsional)</span></label>
        <input wire:model="cfg.catatan" type="text" placeholder="misal: pengiriman rutin minggu ke-3..."
            style="width:100%; border:1.5px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:13px; box-sizing:border-box;" />
    </div>

    {{-- Tombol proses --}}
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <button wire:click="prosesDistribusi" wire:confirm="Proses distribusi {{ $totalDipilih }} unit dari {{ $gudangAsal }} ke {{ $gudangTujuan }}? Aksi ini tidak bisa dibatalkan." type="button"
            style="background:#ea580c; color:#fff; border:none; border-radius:9px; padding:12px 32px; font-size:14px; font-weight:800; cursor:pointer; box-shadow:0 2px 8px rgba(234,88,12,.3);">
            Proses Distribusi — {{ $totalDipilih }} Unit
        </button>
        <button wire:click="resetForm" type="button"
            style="background:#f3f4f6; color:#6b7280; border:none; border-radius:9px; padding:12px 20px; font-size:14px; cursor:pointer; font-weight:500;">
            Reset
        </button>
    </div>
    <p style="font-size:11px; color:#9ca3af; margin:10px 0 0;">Sistem memilih unit otomatis berdasarkan kode unit (terlama dahulu). Posisi tujuan akan langsung diterapkan ke unit yang dipindah.</p>
</div>
@endif

@endif {{-- end gudang dipilih --}}

</x-filament-panels::page>
