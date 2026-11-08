@php
    $units = $page->getUnitsForProduct();

    $sebaranGudang = [];
    foreach ($units as $u) {
        $g = $u['gudang'];
        $sebaranGudang[$g] = ($sebaranGudang[$g] ?? 0) + 1;
    }

    $badgeColors = [
        'success' => ['bg'=>'#dcfce7','color'=>'#15803d'],
        'warning' => ['bg'=>'#fef9c3','color'=>'#a16207'],
        'info'    => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
        'danger'  => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
        'primary' => ['bg'=>'#ffedd5','color'=>'#c2410c'],
        'gray'    => ['bg'=>'#f3f4f6','color'=>'#374151'],
    ];

    $stokCount    = count(array_filter($units, fn($u) => $u['status'] === 'stok'));
    $prosesCount  = count(array_filter($units, fn($u) => in_array($u['status'], ['dipesan','dikirim','sampai'])));
    $selesaiCount = count(array_filter($units, fn($u) => $u['status'] === 'selesai'));

    $sebaranUkuran = [];
    foreach ($units as $u) {
        $uk = $u['ukuran'] ?? '—';
        if ($uk !== '—') $sebaranUkuran[$uk] = ($sebaranUkuran[$uk] ?? 0) + 1;
    }
@endphp

@if(count($units) > 0)
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05); margin-top:16px;">

    {{-- Header --}}
    <div style="padding:14px 20px; background:#fafafa; border-bottom:1px solid #f3f4f6;">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div>
                <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Unit Produk</p>
                <div style="display:flex; align-items:center; gap:10px; margin-top:4px; flex-wrap:wrap;">
                    <span style="font-size:12px; color:#6b7280;">{{ count($units) }} total</span>
                    @if($stokCount > 0)<span style="font-size:11px; background:#dcfce7; color:#15803d; font-weight:600; padding:2px 8px; border-radius:6px;">{{ $stokCount }} stok</span>@endif
                    @if($prosesCount > 0)<span style="font-size:11px; background:#fef9c3; color:#a16207; font-weight:600; padding:2px 8px; border-radius:6px;">{{ $prosesCount }} proses</span>@endif
                    @if($selesaiCount > 0)<span style="font-size:11px; background:#f3f4f6; color:#374151; font-weight:600; padding:2px 8px; border-radius:6px;">{{ $selesaiCount }} selesai</span>@endif
                </div>
                @if(count($sebaranGudang) > 0)
                <div style="display:flex; flex-wrap:wrap; gap:5px; margin-top:7px;">
                    @foreach($sebaranGudang as $namaGudang => $jumlah)
                    <span style="font-size:11px; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:9999px; padding:2px 10px; font-weight:500;">
                        {{ $namaGudang }}: {{ $jumlah }} unit
                    </span>
                    @endforeach
                    @if(count($sebaranGudang) > 1)
                    <span style="font-size:11px; color:#f59e0b; font-weight:600; padding:2px 4px;">
                        · Tersebar di {{ count($sebaranGudang) }} gudang
                    </span>
                    @endif
                </div>
                @endif
                @if(count($sebaranUkuran) > 0)
                <div style="display:flex; flex-wrap:wrap; gap:5px; margin-top:5px;">
                    @foreach($sebaranUkuran as $namaUkuran => $jumlah)
                    <span style="font-size:11px; background:#ede9fe; color:#7c3aed; border:1px solid #ddd6fe; border-radius:9999px; padding:2px 10px; font-weight:600;">
                        {{ $namaUkuran }}: {{ $jumlah }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            {{-- Shortcut ke Distribusi --}}
            <a href="{{ route('filament.admin.pages.distribusi') }}" style="display:inline-flex; align-items:center; gap:6px; background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; border-radius:8px; padding:7px 14px; font-size:12px; font-weight:600; text-decoration:none; flex-shrink:0;">
                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Distribusi Gudang
            </a>
        </div>
        <p style="font-size:11px; color:#9ca3af; margin:10px 0 0;">
            Unit di bawah hanya info. Untuk update status, edit posisi, atau pindah unit: gunakan halaman
            <strong style="color:#6b7280;">Detail Gudang</strong>.
            Untuk distribusi massal: gunakan halaman <strong style="color:#ea580c;">Distribusi</strong>.
        </p>
    </div>

    {{-- Grid unit — pure read-only --}}
    <div style="padding:16px 20px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:8px;">
            @foreach($units as $u)
            @php $bc = $badgeColors[$u['status_color']] ?? $badgeColors['gray']; @endphp
            <div style="border:1.5px solid {{ $bc['color'] }}33; background:{{ $bc['bg'] }}; border-radius:8px; padding:10px 12px;">
                <p style="font-size:11px; font-family:monospace; font-weight:700; color:#111827; margin:0 0 4px; line-height:1.3;">{{ $u['unit_code'] }}</p>
                <div style="display:flex; align-items:center; gap:5px; margin-bottom:4px; flex-wrap:wrap;">
                    <span style="font-size:10px; font-weight:700; color:{{ $bc['color'] }}; background:{{ $bc['color'] }}1a; padding:2px 7px; border-radius:9999px;">{{ $u['status_label'] }}</span>
                    <span style="font-size:10px; color:#9ca3af;">{{ $u['gudang'] }}</span>
                    @if(($u['ukuran'] ?? '—') !== '—')
                    <span style="font-size:10px; font-weight:600; color:#7c3aed; background:#ede9fe; padding:2px 6px; border-radius:9999px;">{{ $u['ukuran'] }}</span>
                    @endif
                </div>
                @if($u['posisi'])
                <p style="font-size:10px; color:#6366f1; margin:0 0 2px; font-weight:500;">📍 {{ $u['posisi'] }}</p>
                @else
                <p style="font-size:10px; color:#d1d5db; margin:0 0 2px; font-style:italic;">Belum ada posisi</p>
                @endif
                <p style="font-size:9px; color:#9ca3af; margin:0;">{{ $u['updated_at'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endif
