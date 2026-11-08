<x-filament-panels::page>
@php
    $record         = $this->record;
    $summary        = $this->getUnitSummary();
    $warehouseOpts  = $this->getWarehouseOptions();
    $activeTab      = $cfg['active_tab']        ?? 'produk';
    $filterStatus   = $cfg['filter_status']     ?? '';
    $search         = $cfg['search']            ?? '';
    $panelPid       = $cfg['pindah_product_id'] ?? null;
    $selectedUnits  = $cfg['selected_units']    ?? [];
    $gudangTujuan   = $cfg['gudang_tujuan_id']  ?? null;
    $statusUnitCode = $cfg['status_unit_code']  ?? null;
    $statusBaru     = $cfg['status_baru']       ?? null;
    $statusCatatan  = $cfg['status_catatan']    ?? '';
    $expandedPids   = $cfg['expanded_products'] ?? [];
    $bulkMode       = $cfg['bulk_mode']         ?? false;
    $bulkSelected   = $cfg['bulk_selected']     ?? [];
    $posisiUnitCode   = $cfg['posisi_unit_code']   ?? null;
    $posisiInput      = $cfg['posisi_input']       ?? '';
    $posisiBulkMode   = $cfg['posisi_bulk_mode']   ?? false;
    $posisiBulkSel    = $cfg['posisi_bulk_sel']    ?? [];
    $posisiBulkInput  = $cfg['posisi_bulk_input']  ?? '';
    $panelTab         = $cfg['panel_tab']           ?? 'status';

    $allProducts = $this->getUnitsByProduct();

    $products = array_values(array_filter($allProducts, function($p) use ($search, $filterStatus) {
        if ($search) {
            $hay = strtolower($p['product_nama'] . ' ' . $p['product_code']);
            if (!str_contains($hay, strtolower($search))) return false;
        }
        if ($filterStatus) {
            $hasStatus = count(array_filter($p['units'], fn($u) => $u['status'] === $filterStatus)) > 0;
            if (!$hasStatus) return false;
        }
        return true;
    }));

    $totalUnit = array_sum(array_column($summary, 'count'));

    $badgeColors = [
        'success' => ['bg'=>'#dcfce7','color'=>'#15803d'],
        'warning' => ['bg'=>'#fef9c3','color'=>'#a16207'],
        'info'    => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
        'danger'  => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
        'primary' => ['bg'=>'#ffedd5','color'=>'#c2410c'],
        'gray'    => ['bg'=>'#f3f4f6','color'=>'#374151'],
    ];
    $statusColors = [
        'stok'         => 'success',
        'dipesan'      => 'warning',
        'dikirim'      => 'info',
        'sampai'       => 'primary',
        'selesai'      => 'gray',
        'dikembalikan' => 'danger',
    ];
    $statusLabels = [
        'stok'         => 'Stok',
        'dipesan'      => 'Dipesan',
        'dikirim'      => 'Dikirim',
        'sampai'       => 'Sampai',
        'selesai'      => 'Selesai',
        'dikembalikan' => 'Dikembalikan',
    ];

    // Cari unit untuk panel bawah (single mode)
    $editUnit = null;
    if ($statusUnitCode && !$bulkMode) {
        foreach ($allProducts as $p) {
            foreach ($p['units'] as $u) {
                if ($u['unit_code'] === $statusUnitCode) {
                    $editUnit = array_merge($u, ['product_nama' => $p['product_nama']]);
                    break 2;
                }
            }
        }
    }

    // Cari unit posisi untuk panel bawah
    $posisiUnit = null;
    if ($posisiUnitCode) {
        foreach ($allProducts as $p) {
            foreach ($p['units'] as $u) {
                if ($u['unit_code'] === $posisiUnitCode) {
                    $posisiUnit = array_merge($u, ['product_nama' => $p['product_nama']]);
                    break 2;
                }
            }
        }
    }

    // Untuk bulk mode: hitung allowed transitions dari semua unit yang dipilih
    $bulkAllowedNext = [];
    if ($bulkMode && count($bulkSelected) > 0) {
        $firstUnit = null;
        foreach ($allProducts as $p) {
            foreach ($p['units'] as $u) {
                if (in_array($u['unit_code'], $bulkSelected)) {
                    if ($firstUnit === null) {
                        $bulkAllowedNext = $u['allowed_next'];
                        $firstUnit = $u['status'];
                    } elseif ($u['status'] !== $firstUnit) {
                        // Unit dengan status berbeda — intersect transitions
                        $bulkAllowedNext = array_intersect($bulkAllowedNext, $u['allowed_next']);
                    }
                }
            }
        }
        $bulkAllowedNext = array_values($bulkAllowedNext);
    }

    $panelAktif = $editUnit || ($bulkMode && count($bulkSelected) > 0) || $posisiUnit || $posisiBulkMode;
@endphp

{{-- TAB NAV --}}
<div style="display:flex; gap:2px; background:#f3f4f6; border-radius:10px; padding:4px; margin-bottom:20px; width:fit-content;">
    <button wire:click="$set('cfg.active_tab','info')" type="button"
        style="padding:7px 20px; border-radius:7px; border:none; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s;
        background:{{ $activeTab==='info' ? '#fff' : 'transparent' }}; color:{{ $activeTab==='info' ? '#111827' : '#6b7280' }};
        box-shadow:{{ $activeTab==='info' ? '0 1px 3px rgba(0,0,0,.1)' : 'none' }};">
        Info Gudang
    </button>
    <button wire:click="$set('cfg.active_tab','produk')" type="button"
        style="padding:7px 20px; border-radius:7px; border:none; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s;
        background:{{ $activeTab==='produk' ? '#fff' : 'transparent' }}; color:{{ $activeTab==='produk' ? '#111827' : '#6b7280' }};
        box-shadow:{{ $activeTab==='produk' ? '0 1px 3px rgba(0,0,0,.1)' : 'none' }};">
        Produk & Unit
        @if($totalUnit > 0)
        <span style="background:#ea580c; color:#fff; font-size:10px; border-radius:9999px; padding:1px 7px; margin-left:4px;">{{ $totalUnit }}</span>
        @endif
    </button>
</div>

{{-- ═══ TAB: INFO GUDANG ═══ --}}
@if($activeTab === 'info')
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,.06);">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
            <p style="font-size:11px; color:#9ca3af; margin:0 0 4px; text-transform:uppercase; letter-spacing:.05em;">Nama Gudang</p>
            <p style="font-size:24px; font-weight:700; color:#111827; margin:0 0 8px;">{{ $record->nama }}</p>
            @if($record->alamat)
            <p style="font-size:13px; color:#6b7280; margin:0; max-width:500px; line-height:1.6;">{{ $record->alamat }}</p>
            @endif
        </div>
        <span style="background:{{ $record->status === 'aktif' ? '#dcfce7' : '#fee2e2' }}; color:{{ $record->status === 'aktif' ? '#15803d' : '#b91c1c' }}; padding:5px 16px; border-radius:9999px; font-size:12px; font-weight:600; height:fit-content;">
            {{ $record->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>
    @if(count($summary) > 0)
    <div style="border-top:1px solid #f3f4f6; padding-top:20px;">
        <p style="font-size:12px; font-weight:600; color:#374151; margin:0 0 12px; text-transform:uppercase; letter-spacing:.05em;">Ringkasan Unit</p>
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            @foreach($summary as $s)
            @php $bc = $badgeColors[$s['status_color']] ?? $badgeColors['gray']; @endphp
            <div style="background:#fafafa; border:1px solid #e5e7eb; border-radius:10px; padding:14px 20px; display:flex; align-items:center; gap:10px;">
                <div style="width:10px; height:10px; border-radius:50%; background:{{ $bc['color'] }};"></div>
                <div>
                    <p style="font-size:20px; font-weight:700; color:#111827; margin:0; line-height:1;">{{ $s['count'] }}</p>
                    <p style="font-size:11px; color:#6b7280; margin:2px 0 0;">{{ $s['status_label'] }}</p>
                </div>
            </div>
            @endforeach
            <div style="background:#fafafa; border:1px solid #e5e7eb; border-radius:10px; padding:14px 20px; display:flex; align-items:center; gap:10px;">
                <div style="width:10px; height:10px; border-radius:50%; background:#6366f1;"></div>
                <div>
                    <p style="font-size:20px; font-weight:700; color:#111827; margin:0; line-height:1;">{{ $totalUnit }}</p>
                    <p style="font-size:11px; color:#6b7280; margin:2px 0 0;">Total Unit</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- ═══ TAB: PRODUK & UNIT ═══ --}}
@if($activeTab === 'produk')

{{-- Toolbar --}}
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 16px; margin-bottom:14px; box-shadow:0 1px 2px rgba(0,0,0,.04);">
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:10px;">
        <div style="flex:1; min-width:180px; position:relative;">
            <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input wire:model.live.debounce.300ms="cfg.search" type="text" placeholder="Cari nama / kode produk..."
                style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:8px 10px 8px 32px; font-size:13px; background:#fafafa; box-sizing:border-box;">
        </div>
        <div style="display:flex; gap:6px;">
            <button wire:click="expandAll" type="button" style="font-size:12px; color:#6366f1; background:#eef2ff; border:1px solid #c7d2fe; border-radius:6px; padding:6px 12px; cursor:pointer; font-weight:500; white-space:nowrap;">Buka Semua</button>
            <button wire:click="collapseAll" type="button" style="font-size:12px; color:#6b7280; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:6px 12px; cursor:pointer; font-weight:500; white-space:nowrap;">Tutup Semua</button>
        </div>
    </div>
    <div style="display:flex; gap:6px; flex-wrap:wrap;">
        @php $tabs = ['' => 'Semua (' . $totalUnit . ')']; foreach($summary as $s) { $tabs[$s['status']] = $s['status_label'] . ' (' . $s['count'] . ')'; } @endphp
        @foreach($tabs as $key => $label)
        @php $isActive = $filterStatus === $key; $bc = $key ? ($badgeColors[$statusColors[$key] ?? 'gray'] ?? $badgeColors['gray']) : ['bg'=>'#111827','color'=>'#fff']; @endphp
        <button wire:click="$set('cfg.filter_status', '{{ $key }}')" type="button"
            style="font-size:11px; font-weight:600; padding:4px 12px; border-radius:9999px; cursor:pointer; border:1.5px solid {{ $isActive ? ($key ? $bc['color'] : '#111827') : '#e5e7eb' }};
            background:{{ $isActive ? ($key ? $bc['bg'] : '#111827') : '#fff' }}; color:{{ $isActive ? ($key ? $bc['color'] : '#fff') : '#6b7280' }}; white-space:nowrap;">
            {{ $label }}
        </button>
        @endforeach
    </div>
</div>

{{-- Banner mode aktif --}}
@if($bulkMode)
<div style="background:#fef3c7; border:1.5px solid #fcd34d; border-radius:10px; padding:10px 14px; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
    <svg style="width:15px; height:15px; color:#d97706; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span style="font-size:12px; color:#92400e; font-weight:600;">Mode update massal aktif — centang unit-unit yang ingin diubah statusnya sekaligus.</span>
    <button wire:click="batalBulk" type="button" style="margin-left:auto; font-size:11px; color:#d97706; background:none; border:1px solid #fcd34d; border-radius:5px; padding:2px 9px; cursor:pointer; font-weight:600; white-space:nowrap;">Batal</button>
</div>
@elseif($statusUnitCode)
<div style="background:#eef2ff; border:1.5px solid #c7d2fe; border-radius:10px; padding:10px 14px; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
    <svg style="width:15px; height:15px; color:#6366f1; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span style="font-size:12px; color:#4338ca; font-weight:500;">Panel update status terbuka di bawah. Klik unit lain untuk ganti pilihan.</span>
    <button wire:click="bukaStatusPanel('{{ $statusUnitCode }}')" type="button" style="margin-left:auto; font-size:11px; color:#6366f1; background:none; border:1px solid #a5b4fc; border-radius:5px; padding:2px 9px; cursor:pointer; font-weight:600; white-space:nowrap;">Tutup Panel</button>
</div>
@elseif($posisiBulkMode)
<div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:10px; padding:10px 14px; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
    <svg style="width:15px; height:15px; color:#16a34a; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <span style="font-size:12px; color:#15803d; font-weight:600;">Mode posisi massal aktif — centang unit, isi posisi, simpan ke semua sekaligus. {{ count($posisiBulkSel) > 0 ? count($posisiBulkSel) . ' unit dipilih.' : '' }}</span>
    <button wire:click="batalPosisiBulk" type="button" style="margin-left:auto; font-size:11px; color:#16a34a; background:none; border:1px solid #86efac; border-radius:5px; padding:2px 9px; cursor:pointer; font-weight:600; white-space:nowrap;">Batal</button>
</div>
@elseif($posisiUnitCode)
<div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:10px; padding:10px 14px; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
    <svg style="width:15px; height:15px; color:#16a34a; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <span style="font-size:12px; color:#15803d; font-weight:500;">Panel edit posisi terbuka di bawah.</span>
    <button wire:click="bukaPosisiPanel('{{ $posisiUnitCode }}', '')" type="button" style="margin-left:auto; font-size:11px; color:#16a34a; background:none; border:1px solid #86efac; border-radius:5px; padding:2px 9px; cursor:pointer; font-weight:600; white-space:nowrap;">Tutup Panel</button>
</div>
@elseif($panelPid)
<div style="background:#fff7ed; border:1.5px solid #fed7aa; border-radius:10px; padding:10px 14px; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
    <svg style="width:15px; height:15px; color:#ea580c; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
    <span style="font-size:12px; color:#92400e; font-weight:500;">Mode pindah gudang aktif — centang unit lalu pilih gudang tujuan.</span>
    <button wire:click="bukaPanel({{ $panelPid }})" type="button" style="margin-left:auto; font-size:11px; color:#ea580c; background:none; border:1px solid #fb923c; border-radius:5px; padding:2px 9px; cursor:pointer; font-weight:600; white-space:nowrap;">Batal</button>
</div>
@endif

{{-- Daftar produk --}}
@if(count($products) === 0)
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:48px; text-align:center; color:#9ca3af; font-size:14px;">
    @if($search || $filterStatus)
        Tidak ada produk yang cocok dengan filter.
        <br><button wire:click="$set('cfg.search','')" type="button" style="margin-top:10px; font-size:12px; color:#6366f1; background:none; border:none; cursor:pointer; text-decoration:underline;">Reset filter</button>
    @else
        Belum ada unit produk di gudang ini.
    @endif
</div>
@else
<div style="display:flex; flex-direction:column; gap:8px;">
    @foreach($products as $p)
    @php
        $pid          = $p['product_id'];
        $isExpanded   = in_array($pid, $expandedPids);
        $panelTerbuka = ($panelPid === $pid);
        $visibleUnits = $filterStatus
            ? array_values(array_filter($p['units'], fn($u) => $u['status'] === $filterStatus))
            : $p['units'];
        $stokCount    = count(array_filter($p['units'], fn($u) => $u['status'] === 'stok'));
        $prosesCount  = count(array_filter($p['units'], fn($u) => in_array($u['status'], ['dipesan','dikirim','sampai'])));
        $selesaiCount = count(array_filter($p['units'], fn($u) => $u['status'] === 'selesai'));
        $bisaPindah   = count(array_filter($p['units'], fn($u) => $u['bisa_pindah']));
        $adaPanelUnit = $statusUnitCode && count(array_filter($p['units'], fn($u) => $u['unit_code'] === $statusUnitCode)) > 0;
        $adaPanelPos  = $posisiUnitCode && count(array_filter($p['units'], fn($u) => $u['unit_code'] === $posisiUnitCode)) > 0;
    @endphp
    <div style="background:#fff; border:2px solid {{ $panelTerbuka ? '#ea580c' : ($adaPanelUnit ? '#6366f1' : ($adaPanelPos ? '#16a34a' : ($isExpanded ? '#d1d5db' : '#e5e7eb'))) }}; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04); transition:border-color .15s;">

        {{-- Header produk --}}
        <div wire:click="toggleExpand({{ $pid }})"
            style="padding:12px 16px; background:{{ $isExpanded ? '#fafafa' : '#fff' }}; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; cursor:pointer; user-select:none;">
            <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                <svg style="width:16px; height:16px; color:#9ca3af; flex-shrink:0; transform:{{ $isExpanded ? 'rotate(90deg)' : 'rotate(0deg)' }}; transition:transform .15s;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <div style="min-width:0;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <span style="font-size:11px; font-family:monospace; color:#6b7280; background:#f3f4f6; padding:1px 7px; border-radius:4px; white-space:nowrap;">{{ $p['product_code'] }}</span>
                        @if($p['kategori'] !== '-')<span style="font-size:11px; color:#9ca3af;">{{ $p['kategori'] }}</span>@endif
                    </div>
                    <p style="font-size:13px; font-weight:600; color:#111827; margin:2px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $p['product_nama'] }}</p>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; flex-shrink:0;" wire:click.stop>
                <span style="font-size:12px; font-weight:600; color:#ea580c;">{{ $p['harga'] }}</span>
                @if($stokCount > 0)<span style="background:#dcfce7; color:#15803d; font-size:10px; font-weight:600; padding:2px 7px; border-radius:6px;">{{ $stokCount }} stok</span>@endif
                @if($prosesCount > 0)<span style="background:#fef9c3; color:#a16207; font-size:10px; font-weight:600; padding:2px 7px; border-radius:6px;">{{ $prosesCount }} proses</span>@endif
                @if($selesaiCount > 0)<span style="background:#f3f4f6; color:#374151; font-size:10px; font-weight:600; padding:2px 7px; border-radius:6px;">{{ $selesaiCount }} selesai</span>@endif
                {{-- Tombol aksi --}}
                <button wire:click="aktifkanBulkMode({{ $pid }})" type="button"
                    style="background:{{ $bulkMode ? '#a5b4fc' : '#eef2ff' }}; color:#6366f1; border:1px solid #a5b4fc; border-radius:6px; padding:3px 10px; font-size:11px; font-weight:600; cursor:pointer; white-space:nowrap;">
                    Update Massal
                </button>
                <button wire:click="aktifkanPosisiBulkMode" type="button"
                    style="background:{{ $posisiBulkMode ? '#bbf7d0' : '#f0fdf4' }}; color:#16a34a; border:1px solid #86efac; border-radius:6px; padding:3px 10px; font-size:11px; font-weight:600; cursor:pointer; white-space:nowrap;">
                    Posisi Massal
                </button>
                @if($bisaPindah > 0)
                <button wire:click="bukaPanel({{ $pid }})" type="button"
                    style="background:{{ $panelTerbuka ? '#ea580c' : '#fff7ed' }}; color:{{ $panelTerbuka ? '#fff' : '#ea580c' }}; border:1px solid #ea580c; border-radius:6px; padding:3px 10px; font-size:11px; font-weight:600; cursor:pointer; white-space:nowrap;">
                    {{ $panelTerbuka ? 'Tutup' : 'Pindah Gudang' }}
                </button>
                @endif
            </div>
        </div>

        {{-- Unit grid --}}
        @if($isExpanded)
        <div style="padding:12px 16px; border-top:1px solid #f3f4f6;">
            <p style="font-size:11px; color:#9ca3af; margin:0 0 10px;">
                @if($posisiBulkMode)
                    <span style="color:#16a34a; font-weight:600;">Centang unit</span> lalu isi posisi di panel bawah — terapkan ke semua sekaligus
                @elseif($bulkMode)
                    <span style="color:#d97706; font-weight:600;">Centang unit</span> untuk update massal — atau klik unit untuk update/edit posisi satu per satu
                @elseif($panelTerbuka)
                    <span style="color:#c2410c; font-weight:600;">Centang unit</span> yang ingin dipindah ke gudang lain
                @else
                    <span style="color:#374151;">Klik unit</span> untuk update status atau edit posisi
                @endif
            </p>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:8px;">
                @foreach($visibleUnits as $u)
                @php
                    $ubc        = $badgeColors[$statusColors[$u['status']] ?? 'gray'] ?? $badgeColors['gray'];
                    $dipilihBulk      = $bulkMode && in_array($u['unit_code'], $bulkSelected);
                    $dipilihPindah    = $panelTerbuka && in_array($u['unit_code'], $selectedUnits);
                    $dipilihPosBulk   = $posisiBulkMode && in_array($u['unit_code'], $posisiBulkSel);
                    $sedangEditStatus = !$bulkMode && !$panelTerbuka && !$posisiBulkMode && $statusUnitCode === $u['unit_code'];
                    $sedangEditPos    = !$bulkMode && !$panelTerbuka && !$posisiBulkMode && $posisiUnitCode === $u['unit_code'];
                    $bisaEdit = count($u['allowed_next']) > 0;
                @endphp
                <div
                    @if($posisiBulkMode) wire:click="togglePosisiBulk('{{ $u['unit_code'] }}')"
                    @elseif($bulkMode && $bisaEdit) wire:click="toggleBulkUnit('{{ $u['unit_code'] }}')"
                    @elseif($panelTerbuka && $u['bisa_pindah']) wire:click="toggleUnit('{{ $u['unit_code'] }}')"
                    @elseif(!$bulkMode && !$panelTerbuka && !$posisiBulkMode) wire:click="bukaStatusPanel('{{ $u['unit_code'] }}')"
                    @endif
                    style="border:2px solid {{ $dipilihPosBulk ? '#16a34a' : ($dipilihBulk ? '#d97706' : ($dipilihPindah ? '#ea580c' : ($sedangEditStatus ? '#6366f1' : ($sedangEditPos ? '#16a34a' : $ubc['color'] . '44')))) }}; background:{{ $dipilihPosBulk ? '#f0fdf4' : ($dipilihBulk ? '#fef9c3' : ($dipilihPindah ? '#fff7ed' : ($sedangEditStatus ? '#eef2ff' : ($sedangEditPos ? '#f0fdf4' : $ubc['bg'])))) }}; border-radius:8px; padding:10px 12px; cursor:pointer; transition:all .1s; position:relative;">

                    {{-- Checkbox posisi bulk / status bulk / pindah --}}
                    @if($posisiBulkMode)
                    <div style="position:absolute; top:7px; right:7px; width:16px; height:16px; border-radius:3px; border:2px solid {{ $dipilihPosBulk ? '#16a34a' : '#d1d5db' }}; background:{{ $dipilihPosBulk ? '#16a34a' : '#fff' }}; display:flex; align-items:center; justify-content:center;">
                        @if($dipilihPosBulk)<svg style="width:10px;height:10px;" fill="none" viewBox="0 0 12 12" stroke="#fff" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>@endif
                    </div>
                    @elseif($bulkMode && $bisaEdit)
                    <div style="position:absolute; top:7px; right:7px; width:16px; height:16px; border-radius:3px; border:2px solid {{ $dipilihBulk ? '#d97706' : '#d1d5db' }}; background:{{ $dipilihBulk ? '#d97706' : '#fff' }}; display:flex; align-items:center; justify-content:center;">
                        @if($dipilihBulk)<svg style="width:10px;height:10px;" fill="none" viewBox="0 0 12 12" stroke="#fff" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>@endif
                    </div>
                    @elseif($panelTerbuka)
                    <div style="position:absolute; top:7px; right:7px; width:16px; height:16px; border-radius:3px; border:2px solid {{ $dipilihPindah ? '#ea580c' : ($u['bisa_pindah'] ? '#d1d5db' : '#e5e7eb') }}; background:{{ $dipilihPindah ? '#ea580c' : '#fff' }}; display:flex; align-items:center; justify-content:center; opacity:{{ $u['bisa_pindah'] ? '1' : '.3' }};">
                        @if($dipilihPindah)<svg style="width:10px;height:10px;" fill="none" viewBox="0 0 12 12" stroke="#fff" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>@endif
                    </div>
                    @endif

                    {{-- Konten unit --}}
                    <p style="font-size:11px; font-family:monospace; font-weight:700; color:#111827; margin:0 0 4px; padding-right:{{ ($bulkMode && $bisaEdit) || $panelTerbuka ? '20px' : '0' }}; line-height:1.3;">{{ $u['unit_code'] }}</p>
                    <div style="display:flex; flex-wrap:wrap; gap:3px; margin-bottom:4px;">
                        <span style="display:inline-block; font-size:10px; font-weight:700; color:{{ $ubc['color'] }}; background:{{ $ubc['color'] }}1a; padding:2px 8px; border-radius:9999px;">{{ $u['status_label'] }}</span>
                        @if(!empty($u['ukuran']))<span style="display:inline-block; font-size:10px; font-weight:700; color:#7c3aed; background:#ede9fe; padding:2px 7px; border-radius:9999px;">{{ $u['ukuran'] }}</span>@endif
                    </div>
                    @if($u['posisi'])
                    <p style="font-size:10px; color:#6366f1; margin:0 0 2px; font-weight:500;">📍 {{ $u['posisi'] }}</p>
                    @else
                    <p style="font-size:10px; color:#d1d5db; margin:0 0 2px; font-style:italic;">Belum ada posisi</p>
                    @endif
                    <p style="font-size:9px; color:#9ca3af; margin:0;">{{ $u['updated_at'] }}</p>

                    {{-- Indikator panel aktif --}}
                    @if($sedangEditStatus)
                    <div style="position:absolute; top:5px; right:5px; width:8px; height:8px; border-radius:50%; background:#6366f1;"></div>
                    @elseif($sedangEditPos)
                    <div style="position:absolute; top:5px; right:5px; width:8px; height:8px; border-radius:50%; background:#16a34a;"></div>
                    @endif

                    {{-- Tombol edit posisi (muncul saat klik unit, tidak dalam mode bulk/pindah) --}}
                    @if(!$bulkMode && !$panelTerbuka && $sedangEditStatus)
                    <div style="margin-top:6px; padding-top:6px; border-top:1px solid #c7d2fe;" wire:click.stop>
                        <button wire:click="bukaPosisiPanel('{{ $u['unit_code'] }}', '{{ addslashes($u['posisi'] ?? '') }}')" type="button"
                            style="width:100%; background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:5px; padding:4px 0; font-size:10px; font-weight:600; cursor:pointer; margin-top:2px;">
                            📍 Edit Posisi
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Posisi bulk: pilih semua --}}
            @if($posisiBulkMode)
            <div style="margin-top:12px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-size:12px; color:#15803d; font-weight:600;">{{ count($posisiBulkSel) }} unit dipilih</span>
                <button wire:click="pilihSemuaPosisiBulk({{ $pid }})" type="button" style="font-size:11px; color:#15803d; background:#dcfce7; border:1px solid #86efac; border-radius:5px; padding:3px 10px; cursor:pointer; font-weight:500;">Pilih Semua ({{ count($p['units']) }})</button>
                <button wire:click="$set('cfg.posisi_bulk_sel', [])" type="button" style="font-size:11px; color:#6b7280; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:5px; padding:3px 10px; cursor:pointer;">Batal Pilih</button>
            </div>
            @endif

            {{-- Panel bulk mode: pilih semua + info jumlah --}}
            @if($bulkMode)
            <div style="margin-top:12px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-size:12px; color:#92400e; font-weight:600;">{{ count($bulkSelected) }} unit dipilih</span>
                @if($stokCount > 0)
                <button wire:click="pilihSemuaBulk({{ $pid }}, 'stok')" type="button" style="font-size:11px; color:#15803d; background:#dcfce7; border:1px solid #86efac; border-radius:5px; padding:3px 10px; cursor:pointer; font-weight:500;">Pilih semua Stok ({{ $stokCount }})</button>
                @endif
                @if($prosesCount > 0)
                <button wire:click="pilihSemuaBulk({{ $pid }}, 'dipesan')" type="button" style="font-size:11px; color:#a16207; background:#fef9c3; border:1px solid #fde68a; border-radius:5px; padding:3px 10px; cursor:pointer; font-weight:500;">Pilih semua Dipesan</button>
                @endif
                <button wire:click="$set('cfg.bulk_selected', [])" type="button" style="font-size:11px; color:#6b7280; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:5px; padding:3px 10px; cursor:pointer;">Batal Pilih</button>
            </div>
            @endif

            {{-- Panel pindah gudang --}}
            @if($panelTerbuka)
            <div style="margin-top:12px; padding:14px 16px; background:#fff7ed; border:1px solid #fed7aa; border-radius:10px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:6px;">
                    <div>
                        <p style="font-size:13px; font-weight:700; color:#92400e; margin:0;">Pindah Gudang</p>
                        <p style="font-size:11px; color:#b45309; margin:3px 0 0;">{{ count($selectedUnits) }} unit dipilih dari {{ $bisaPindah }} yang bisa dipindah</p>
                    </div>
                    <div style="display:flex; gap:5px;">
                        <button wire:click="pilihSemua({{ $pid }})" style="font-size:11px; color:#ea580c; background:#fff; border:1px solid #ea580c; border-radius:5px; padding:4px 10px; cursor:pointer; font-weight:600;">Pilih Semua</button>
                        <button wire:click="batalSemua" style="font-size:11px; color:#6b7280; background:#fff; border:1px solid #d1d5db; border-radius:5px; padding:4px 10px; cursor:pointer;">Batal Pilih</button>
                    </div>
                </div>
                @if(count($warehouseOpts) > 0)
                <div style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;">
                    <div style="flex:1; min-width:180px;">
                        <label style="display:block; font-size:11px; font-weight:600; color:#92400e; margin-bottom:4px;">Gudang Tujuan</label>
                        <select wire:model="cfg.gudang_tujuan_id" style="width:100%; border:1px solid #fed7aa; border-radius:7px; padding:8px 10px; font-size:13px; background:#fff; box-sizing:border-box;">
                            <option value="">-- Pilih gudang tujuan --</option>
                            @foreach($warehouseOpts as $wid => $wnama)
                            <option value="{{ $wid }}" @selected($gudangTujuan == $wid)>{{ $wnama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="pindahkan" type="button"
                        style="background:{{ count($selectedUnits) > 0 && $gudangTujuan ? '#ea580c' : '#d1d5db' }}; color:#fff; border:none; border-radius:7px; padding:9px 20px; font-size:13px; font-weight:700; cursor:{{ count($selectedUnits) > 0 && $gudangTujuan ? 'pointer' : 'not-allowed' }}; white-space:nowrap;">
                        Pindahkan{{ count($selectedUnits) > 0 ? ' ' . count($selectedUnits) . ' Unit' : '' }}
                    </button>
                </div>
                @else
                <p style="font-size:12px; color:#b45309; background:#fef3c7; border:1px solid #fde68a; border-radius:7px; padding:9px 12px; margin:0;">
                    Belum ada gudang lain yang aktif.
                </p>
                @endif
            </div>
            @endif
        </div>
        @endif

    </div>
    @endforeach
</div>
@endif

{{-- ═══ PANEL BAWAH STICKY ═══ --}}
@if($panelAktif && !$panelPid)
<div style="position:sticky; bottom:0; z-index:20; margin-top:16px;">
    <div style="background:#fff; border:2px solid {{ $posisiUnit ? '#16a34a' : '#6366f1' }}; border-radius:12px; padding:18px 20px; box-shadow:0 -6px 24px rgba(0,0,0,.12);">

        {{-- ── Tab pilih panel (status / posisi) jika unit diklik ── --}}
        @if($editUnit && !$bulkMode)
        <div style="display:flex; gap:0; margin-bottom:14px; border-bottom:2px solid #e5e7eb;">
            <button wire:click="$set('cfg.panel_tab','status')" type="button"
                style="padding:7px 18px; border:none; background:none; font-size:13px; font-weight:700; cursor:pointer;
                color:{{ $panelTab==='status' ? '#6366f1' : '#9ca3af' }};
                border-bottom:2px solid {{ $panelTab==='status' ? '#6366f1' : 'transparent' }}; margin-bottom:-2px;">
                Update Status
            </button>
            <button wire:click="bukaPosisiPanel('{{ $editUnit['unit_code'] }}', '{{ addslashes($editUnit['posisi'] ?? '') }}')" type="button"
                style="padding:7px 18px; border:none; background:none; font-size:13px; font-weight:700; cursor:pointer;
                color:{{ $panelTab==='posisi' ? '#16a34a' : '#9ca3af' }};
                border-bottom:2px solid {{ $panelTab==='posisi' ? '#16a34a' : 'transparent' }}; margin-bottom:-2px;">
                Edit Posisi
            </button>
            <div style="flex:1;"></div>
            <button wire:click="bukaStatusPanel('{{ $editUnit['unit_code'] }}')" type="button"
                style="font-size:12px; color:#9ca3af; background:none; border:none; cursor:pointer; padding:7px 10px;">
                ✕ Tutup
            </button>
        </div>
        @endif

        {{-- ── Tab: Update Status (single) ── --}}
        @if(($editUnit && !$bulkMode && $panelTab === 'status') || ($bulkMode && count($bulkSelected) > 0))
        <div>
            {{-- Header --}}
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
                <div>
                    @if($bulkMode)
                    <p style="font-size:11px; color:#9ca3af; margin:0 0 2px; text-transform:uppercase; letter-spacing:.04em;">Update Massal — {{ count($bulkSelected) }} Unit Dipilih</p>
                    <p style="font-size:12px; color:#6b7280; margin:0;">Semua unit yang dipilih akan diubah ke status yang sama</p>
                    @else
                    <p style="font-size:11px; color:#9ca3af; margin:0 0 2px; text-transform:uppercase; letter-spacing:.04em;">Update Status Unit</p>
                    <p style="font-size:14px; font-weight:700; color:#111827; margin:0; font-family:monospace;">{{ $editUnit['unit_code'] }}</p>
                    <p style="font-size:12px; color:#6b7280; margin:2px 0 0;">{{ $editUnit['product_nama'] }}</p>
                    @endif
                </div>
                @if(!$bulkMode)
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                    @php $curbc = $badgeColors[$statusColors[$editUnit['status']] ?? 'gray'] ?? $badgeColors['gray']; @endphp
                    <span style="background:{{ $curbc['bg'] }}; color:{{ $curbc['color'] }}; font-size:13px; font-weight:700; padding:5px 14px; border-radius:9999px; border:1.5px solid {{ $curbc['color'] }}44;">
                        {{ $statusLabels[$editUnit['status']] ?? $editUnit['status'] }}
                    </span>
                    <svg style="width:14px; height:14px; color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <span style="font-size:13px; font-weight:600; color:#6366f1;">{{ $statusBaru ? ($statusLabels[$statusBaru] ?? $statusBaru) : '?' }}</span>
                </div>
                @endif
            </div>

            {{-- Tombol status --}}
            @php $allowedNext = $bulkMode ? $bulkAllowedNext : ($editUnit['allowed_next'] ?? []); @endphp
            @if(count($allowedNext) > 0)
            <div style="margin-bottom:12px;">
                <p style="font-size:11px; font-weight:700; color:#374151; margin:0 0 8px; text-transform:uppercase; letter-spacing:.04em;">Ubah ke status:</p>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach($allowedNext as $next)
                    @php $nbc = $badgeColors[$statusColors[$next] ?? 'gray'] ?? $badgeColors['gray']; @endphp
                    <button wire:click="$set('cfg.status_baru', '{{ $next }}')" type="button"
                        style="font-size:13px; font-weight:700; padding:9px 22px; border-radius:8px; cursor:pointer; transition:all .1s;
                        border:2px solid {{ $statusBaru === $next ? $nbc['color'] : '#e5e7eb' }};
                        background:{{ $statusBaru === $next ? $nbc['bg'] : '#fafafa' }};
                        color:{{ $statusBaru === $next ? $nbc['color'] : '#374151' }};
                        box-shadow:{{ $statusBaru === $next ? '0 0 0 3px ' . $nbc['color'] . '22' : 'none' }};">
                        {{ $statusLabels[$next] ?? $next }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:4px;">Catatan (opsional)</label>
                <input wire:model="cfg.status_catatan" type="text" placeholder="Misal: dikirim via JNE, resi #123..."
                    style="width:100%; border:1.5px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:13px; box-sizing:border-box;">
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button wire:click="{{ $bulkMode ? 'updateStatusBulk' : 'updateStatusUnit' }}" type="button"
                    style="background:{{ $statusBaru ? '#6366f1' : '#d1d5db' }}; color:#fff; border:none; border-radius:8px; padding:10px 28px; font-size:13px; font-weight:700; cursor:{{ $statusBaru ? 'pointer' : 'not-allowed' }};">
                    @if($statusBaru)
                        @if($bulkMode)Simpan — Ubah {{ count($bulkSelected) }} Unit ke {{ $statusLabels[$statusBaru] ?? $statusBaru }}
                        @else Simpan — Ubah ke {{ $statusLabels[$statusBaru] ?? $statusBaru }}@endif
                    @else Pilih status dulu @endif
                </button>
                @if($bulkMode)
                <button wire:click="batalBulk" type="button" style="background:#f3f4f6; color:#6b7280; border:none; border-radius:8px; padding:10px 16px; font-size:13px; cursor:pointer;">Batal</button>
                @endif
            </div>
            @else
            <p style="font-size:13px; color:#9ca3af; background:#f9fafb; border-radius:8px; padding:12px 16px; margin:0;">
                @if($bulkMode && count($bulkSelected) > 0)
                    Unit yang dipilih memiliki status berbeda-beda — tidak ada transisi yang berlaku untuk semua. Pilih unit dengan status yang sama.
                @else
                    Status <strong>{{ $statusLabels[$editUnit['status'] ?? ''] ?? '-' }}</strong> adalah status akhir.
                @endif
            </p>
            @endif
        </div>
        @endif

        {{-- ── Panel: Posisi Massal (hint saat belum pilih unit) ── --}}
        @if($posisiBulkMode && count($posisiBulkSel) === 0 && !$editUnit && !$posisiUnit)
        <div style="text-align:center; padding:16px; color:#9ca3af; font-size:13px;">
            <svg style="width:28px; height:28px; margin:0 auto 8px; display:block; color:#d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Centang unit di atas untuk memilih mana yang akan diubah posisinya
            <br><button wire:click="batalPosisiBulk" type="button" style="margin-top:8px; font-size:11px; color:#9ca3af; background:none; border:1px solid #e5e7eb; border-radius:5px; padding:3px 10px; cursor:pointer;">Batal</button>
        </div>
        @endif

        {{-- ── Panel: Posisi Massal ── --}}
        @if($posisiBulkMode && count($posisiBulkSel) > 0 && !$editUnit && !$posisiUnit)
        <div>
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
                <div>
                    <p style="font-size:11px; color:#9ca3af; margin:0 0 2px; text-transform:uppercase; letter-spacing:.04em;">Edit Posisi Massal</p>
                    <p style="font-size:14px; font-weight:700; color:#111827; margin:0;">{{ count($posisiBulkSel) }} unit dipilih</p>
                    <p style="font-size:12px; color:#6b7280; margin:2px 0 0;">Posisi yang sama akan diterapkan ke semua unit yang dipilih</p>
                </div>
                <button wire:click="batalPosisiBulk" type="button" style="font-size:12px; color:#9ca3af; background:none; border:none; cursor:pointer; padding:5px 8px;">✕ Batal</button>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">Posisi Baru (Rak/Slot)</label>
                <input wire:model="cfg.posisi_bulk_input" type="text" placeholder="contoh: Rak A3, Slot 1 — atau kosongkan untuk hapus posisi"
                    style="width:100%; border:2px solid #86efac; border-radius:8px; padding:10px 14px; font-size:14px; box-sizing:border-box;">
                <p style="font-size:11px; color:#9ca3af; margin:5px 0 0;">Kosongkan untuk menghapus posisi dari semua unit yang dipilih.</p>
            </div>
            {{-- Preview unit yang dipilih --}}
            <div style="display:flex; flex-wrap:wrap; gap:5px; margin-bottom:14px;">
                @foreach($posisiBulkSel as $code)
                <span style="font-size:10px; font-family:monospace; background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:4px; padding:2px 8px;">{{ $code }}</span>
                @endforeach
            </div>
            <div style="display:flex; gap:8px;">
                <button wire:click="simpanPosisiBulk" type="button"
                    style="background:#16a34a; color:#fff; border:none; border-radius:8px; padding:10px 28px; font-size:13px; font-weight:700; cursor:pointer;">
                    Simpan ke {{ count($posisiBulkSel) }} Unit
                </button>
                <button wire:click="batalPosisiBulk" type="button"
                    style="background:#f3f4f6; color:#6b7280; border:none; border-radius:8px; padding:10px 16px; font-size:13px; cursor:pointer;">
                    Batal
                </button>
            </div>
        </div>
        @endif

        {{-- ── Tab: Edit Posisi ── --}}
        @if($posisiUnit)
        <div>
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
                <div>
                    <p style="font-size:11px; color:#9ca3af; margin:0 0 2px; text-transform:uppercase; letter-spacing:.04em;">Edit Posisi (Rak / Slot)</p>
                    <p style="font-size:14px; font-weight:700; color:#111827; margin:0; font-family:monospace;">{{ $posisiUnit['unit_code'] }}</p>
                    <p style="font-size:12px; color:#6b7280; margin:2px 0 0;">{{ $posisiUnit['product_nama'] }}</p>
                </div>
                @if($posisiUnit['posisi'])
                <span style="font-size:12px; color:#6366f1; background:#eef2ff; border:1px solid #c7d2fe; padding:4px 12px; border-radius:8px; font-weight:500;">
                    Posisi saat ini: {{ $posisiUnit['posisi'] }}
                </span>
                @endif
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">Posisi Baru (Rak/Slot)</label>
                <input wire:model="cfg.posisi_input" type="text" placeholder="contoh: Rak A3, Slot 2"
                    style="width:100%; border:2px solid #86efac; border-radius:8px; padding:10px 14px; font-size:14px; box-sizing:border-box; outline:none;">
                <p style="font-size:11px; color:#9ca3af; margin:5px 0 0;">Kosongkan untuk menghapus posisi.</p>
            </div>
            <div style="display:flex; gap:8px;">
                <button wire:click="simpanPosisiUnit" type="button"
                    style="background:#16a34a; color:#fff; border:none; border-radius:8px; padding:10px 28px; font-size:13px; font-weight:700; cursor:pointer;">
                    Simpan Posisi
                </button>
                <button wire:click="bukaPosisiPanel('{{ $posisiUnit['unit_code'] }}', '')" type="button"
                    style="background:#f3f4f6; color:#6b7280; border:none; border-radius:8px; padding:10px 16px; font-size:13px; cursor:pointer;">
                    Batal
                </button>
            </div>
        </div>
        @endif

    </div>
</div>
@endif

@endif {{-- end tab produk --}}

</x-filament-panels::page>
