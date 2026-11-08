<x-filament-panels::page>
@php
    $filterTipe      = $this->cfg['filter_tipe']      ?? '';
    $filterWarehouse = $this->cfg['filter_warehouse']  ?? '';
    $filterProduct   = $this->cfg['filter_product']    ?? '';
    $filterDateFrom  = $this->cfg['filter_date_from']  ?? '';
    $filterDateTo    = $this->cfg['filter_date_to']    ?? '';
    $perPage         = (int) ($this->cfg['per_page']   ?? 20);

    $tipeOptions    = $this->getTipeOptions();
    $gudangOptions  = $this->getGudangOptions();
    $productOptions = $this->getProductOptions();
    $tipeColors     = \App\Models\InventoryTransaction::TIPE_COLORS;

    $summary  = $this->getSummary();
    $result   = $this->getTransaksi();
    $rows     = $result['data'];
    $total    = $result['total'];
    $lastPage = $result['last_page'];
    $curPage  = $result['current_page'];
    $from     = $result['from'];
    $to       = $result['to'];

    $colorMap = [
        'success' => ['bg' => '#d1fae5', 'text' => '#065f46', 'border' => '#6ee7b7'],
        'warning' => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fcd34d'],
        'info'    => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#93c5fd'],
        'gray'    => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#d1d5db'],
        'danger'  => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fca5a5'],
    ];

    $statusLabels = \App\Models\ProductUnit::STATUSES ?? [
        'stok' => 'Stok', 'dipesan' => 'Dipesan', 'dikirim' => 'Dikirim',
        'sampai' => 'Sampai', 'selesai' => 'Selesai', 'dikembalikan' => 'Dikembalikan',
    ];

    $totalFiltered = array_sum(array_column($summary, 'count'));
@endphp

<style>
.lpt-wrap { font-family: inherit; }
.lpt-summary { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.lpt-sum-card {
    flex: 1; min-width: 130px; border-radius: 10px; padding: 12px 16px;
    border: 1.5px solid; display: flex; flex-direction: column; gap: 2px;
}
.lpt-sum-card .lbl { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
.lpt-sum-card .cnt { font-size: 22px; font-weight: 700; }
.lpt-sum-card .sub { font-size: 11px; }

.lpt-filters {
    background: var(--lpt-card-bg, #fff);
    border: 1px solid var(--lpt-border, #e5e7eb);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 14px;
    display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;
}
.lpt-fgroup { display: flex; flex-direction: column; gap: 4px; min-width: 130px; flex: 1; }
.lpt-fgroup label { font-size: 11px; font-weight: 600; color: #6b7280; }
.lpt-fgroup select, .lpt-fgroup input {
    padding: 7px 10px; border-radius: 7px; border: 1px solid #d1d5db;
    font-size: 13px; background: inherit; color: inherit;
    width: 100%; box-sizing: border-box;
}
.lpt-fgroup select:focus, .lpt-fgroup input:focus {
    outline: none; border-color: #6366f1; box-shadow: 0 0 0 2px #e0e7ff;
}
.lpt-fbtns { display: flex; gap: 8px; align-items: flex-end; }
.lpt-btn {
    padding: 7px 14px; border-radius: 7px; font-size: 13px; font-weight: 600;
    border: none; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 5px;
}
.lpt-btn-primary { background: #6366f1; color: #fff; }
.lpt-btn-primary:hover { background: #4f46e5; }
.lpt-btn-gray { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
.lpt-btn-gray:hover { background: #e5e7eb; }
.lpt-btn-green { background: #059669; color: #fff; }
.lpt-btn-green:hover { background: #047857; }

.lpt-info { font-size: 12px; color: #6b7280; margin-bottom: 8px; }

.lpt-table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid var(--lpt-border, #e5e7eb); }
.lpt-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.lpt-table thead { background: var(--lpt-head-bg, #f9fafb); }
.lpt-table th {
    padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .5px; color: #6b7280;
    border-bottom: 1.5px solid var(--lpt-border, #e5e7eb);
    white-space: nowrap;
}
.lpt-table td {
    padding: 9px 12px; vertical-align: top;
    border-bottom: 1px solid var(--lpt-border, #e5e7eb);
}
.lpt-table tbody tr:last-child td { border-bottom: none; }
.lpt-table tbody tr:hover { background: var(--lpt-hover-bg, #f9fafb); }

.lpt-badge {
    display: inline-flex; align-items: center; padding: 2px 8px;
    border-radius: 999px; font-size: 11px; font-weight: 600; border: 1px solid;
    white-space: nowrap;
}
.lpt-mono { font-family: monospace; font-size: 12px; color: #374151; }
.lpt-dim { color: #9ca3af; font-size: 12px; }

.lpt-pagination {
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 10px; padding: 12px 0;
}
.lpt-pages { display: flex; gap: 4px; }
.lpt-pg {
    padding: 4px 10px; border-radius: 6px; font-size: 13px; cursor: pointer;
    border: 1px solid #d1d5db; background: inherit; color: inherit;
}
.lpt-pg:hover { background: #f3f4f6; }
.lpt-pg.active { background: #6366f1; color: #fff; border-color: #6366f1; }
.lpt-pg:disabled { opacity: .4; cursor: default; }

.lpt-empty { text-align: center; color: #9ca3af; padding: 32px; font-size: 14px; }

@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) .lpt-filters { --lpt-card-bg: #1f2937; --lpt-border: #374151; }
    :root:not([data-theme="light"]) .lpt-table thead { --lpt-head-bg: #111827; }
    :root:not([data-theme="light"]) .lpt-table tbody tr:hover { --lpt-hover-bg: #1f2937; }
    :root:not([data-theme="light"]) .lpt-table-wrap { --lpt-border: #374151; }
    :root:not([data-theme="light"]) .lpt-fgroup select,
    :root:not([data-theme="light"]) .lpt-fgroup input { border-color: #374151; background: #1f2937; color: #f9fafb; }
    :root:not([data-theme="light"]) .lpt-btn-gray { background: #374151; color: #f9fafb; border-color: #4b5563; }
    :root:not([data-theme="light"]) .lpt-btn-gray:hover { background: #4b5563; }
    :root:not([data-theme="light"]) .lpt-pg { border-color: #374151; color: #f9fafb; }
    :root:not([data-theme="light"]) .lpt-pg:hover { background: #374151; }
}
:root[data-theme="dark"] .lpt-filters { --lpt-card-bg: #1f2937; --lpt-border: #374151; }
:root[data-theme="dark"] .lpt-table thead { --lpt-head-bg: #111827; }
:root[data-theme="dark"] .lpt-table tbody tr:hover { --lpt-hover-bg: #1f2937; }
:root[data-theme="dark"] .lpt-table-wrap { --lpt-border: #374151; }
:root[data-theme="dark"] .lpt-fgroup select,
:root[data-theme="dark"] .lpt-fgroup input { border-color: #374151; background: #1f2937; color: #f9fafb; }
:root[data-theme="dark"] .lpt-btn-gray { background: #374151; color: #f9fafb; border-color: #4b5563; }
:root[data-theme="dark"] .lpt-btn-gray:hover { background: #4b5563; }
:root[data-theme="dark"] .lpt-pg { border-color: #374151; color: #f9fafb; }
:root[data-theme="dark"] .lpt-pg:hover { background: #374151; }
</style>

<div class="lpt-wrap">

    {{-- Summary Cards --}}
    <div class="lpt-summary">
        @foreach($summary as $s)
            @php $c = $colorMap[$s['color']] ?? $colorMap['gray']; @endphp
            <div class="lpt-sum-card" style="background:{{ $c['bg'] }};border-color:{{ $c['border'] }};color:{{ $c['text'] }};">
                <span class="lbl">{{ $s['label'] }}</span>
                <span class="cnt">{{ $s['count'] }}</span>
                <span class="sub">{{ $s['total_unit'] }} unit</span>
            </div>
        @endforeach
    </div>

    {{-- Filter Panel --}}
    <div class="lpt-filters">
        <div class="lpt-fgroup">
            <label>Tipe Transaksi</label>
            <select wire:model="cfg.filter_tipe">
                <option value="">Semua Tipe</option>
                @foreach($tipeOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="lpt-fgroup">
            <label>Gudang</label>
            <select wire:model="cfg.filter_warehouse">
                <option value="">Semua Gudang</option>
                @foreach($gudangOptions as $id => $nama)
                    <option value="{{ $id }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="lpt-fgroup">
            <label>Produk</label>
            <select wire:model="cfg.filter_product">
                <option value="">Semua Produk</option>
                @foreach($productOptions as $id => $nama)
                    <option value="{{ $id }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="lpt-fgroup" style="min-width:120px;">
            <label>Dari Tanggal</label>
            <input type="date" wire:model="cfg.filter_date_from" />
        </div>
        <div class="lpt-fgroup" style="min-width:120px;">
            <label>Sampai Tanggal</label>
            <input type="date" wire:model="cfg.filter_date_to" />
        </div>
        <div class="lpt-fbtns">
            <button class="lpt-btn lpt-btn-primary" wire:click="applyFilter">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                Filter
            </button>
            <button class="lpt-btn lpt-btn-gray" wire:click="resetFilter">Reset</button>
            <button class="lpt-btn lpt-btn-green" wire:click="exportExcel">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    {{-- Info Rows --}}
    <div class="lpt-info">
        Menampilkan {{ $from }}–{{ $to }} dari {{ number_format($total) }} transaksi
        @if($filterTipe || $filterWarehouse || $filterProduct || $filterDateFrom || $filterDateTo)
            (difilter)
        @endif
    </div>

    {{-- Table --}}
    <div class="lpt-table-wrap">
        <table class="lpt-table">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Produk</th>
                    <th>Kode Unit</th>
                    <th>Gudang</th>
                    <th>Jml</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $tipeColor = $tipeColors[$row->tipe] ?? 'gray';
                        $tc = $colorMap[$tipeColor] ?? $colorMap['gray'];
                    @endphp
                    <tr>
                        <td class="lpt-mono">{{ $row->nomor }}</td>
                        <td style="white-space:nowrap;font-size:12px;">
                            {{ $row->created_at->format('d M Y') }}<br>
                            <span class="lpt-dim">{{ $row->created_at->format('H:i') }}</span>
                        </td>
                        <td>
                            <span class="lpt-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['text'] }};border-color:{{ $tc['border'] }};">
                                {{ \App\Models\InventoryTransaction::TIPE_LABELS[$row->tipe] ?? $row->tipe }}
                            </span>
                        </td>
                        <td>
                            @if($row->product)
                                <div style="font-size:13px;font-weight:600;">{{ $row->product->nama }}</div>
                                <div class="lpt-dim">{{ $row->product->product_code }}</div>
                            @else
                                <span class="lpt-dim">-</span>
                            @endif
                        </td>
                        <td>
                            @if($row->unit_code)
                                <span class="lpt-mono">{{ $row->unit_code }}</span>
                            @else
                                <span class="lpt-dim">-</span>
                            @endif
                        </td>
                        <td style="font-size:12px;">
                            @if($row->warehouse)
                                <div>{{ $row->warehouse->nama }}</div>
                            @else
                                <span class="lpt-dim">-</span>
                            @endif
                            @if($row->warehouseTujuan)
                                <div style="color:#6b7280;">→ {{ $row->warehouseTujuan->nama }}</div>
                            @endif
                        </td>
                        <td style="text-align:right;font-weight:600;">{{ $row->jumlah ?? '-' }}</td>
                        <td style="font-size:12px;">
                            @if($row->status_dari && $row->status_ke)
                                <div style="color:#6b7280;">{{ $statusLabels[$row->status_dari] ?? $row->status_dari }}</div>
                                <div style="font-weight:600;">→ {{ $statusLabels[$row->status_ke] ?? $row->status_ke }}</div>
                            @else
                                <span class="lpt-dim">-</span>
                            @endif
                        </td>
                        <td style="font-size:12px;max-width:140px;word-break:break-word;">
                            {{ $row->catatan ?: '-' }}
                        </td>
                        <td style="font-size:12px;white-space:nowrap;">
                            {{ $row->createdBy?->name ?? 'Sistem' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="lpt-empty">
                            Belum ada data transaksi
                            @if($filterTipe || $filterWarehouse || $filterProduct || $filterDateFrom || $filterDateTo)
                                yang sesuai filter
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($lastPage > 1)
        <div class="lpt-pagination">
            <div class="lpt-info">Halaman {{ $curPage }} dari {{ $lastPage }}</div>
            <div class="lpt-pages">
                <button class="lpt-pg" wire:click="goToPage(1)" @if($curPage <= 1) disabled @endif>«</button>
                <button class="lpt-pg" wire:click="goToPage({{ $curPage - 1 }})" @if($curPage <= 1) disabled @endif>‹</button>
                @php
                    $start = max(1, $curPage - 2);
                    $end   = min($lastPage, $curPage + 2);
                @endphp
                @for($p = $start; $p <= $end; $p++)
                    <button class="lpt-pg {{ $p === $curPage ? 'active' : '' }}" wire:click="goToPage({{ $p }})">{{ $p }}</button>
                @endfor
                <button class="lpt-pg" wire:click="goToPage({{ $curPage + 1 }})" @if($curPage >= $lastPage) disabled @endif>›</button>
                <button class="lpt-pg" wire:click="goToPage({{ $lastPage }})" @if($curPage >= $lastPage) disabled @endif>»</button>
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
