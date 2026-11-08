@php $d = $this->getData(); @endphp

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:14px; margin-bottom:4px;">

    {{-- ALUR STATUS UNIT --}}
    <div style="grid-column:1/-1; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0 0 12px;">Alur Status Unit Produk</p>
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:6px;">
            @php
                $alur = [
                    ['stok',         'Stok',         '#15803d', '#dcfce7'],
                    ['dipesan',      'Dipesan',       '#a16207', '#fef9c3'],
                    ['dikirim',      'Dikirim',       '#1d4ed8', '#dbeafe'],
                    ['sampai',       'Sampai',        '#c2410c', '#ffedd5'],
                    ['selesai',      'Selesai',       '#374151', '#f3f4f6'],
                ];
                $cabang = [
                    ['dikembalikan', 'Dikembalikan',  '#b91c1c', '#fee2e2'],
                ];
            @endphp
            @foreach($alur as $i => $s)
            <div style="background:{{ $s[3] }}; color:{{ $s[2] }}; border-radius:9999px; padding:5px 14px; font-size:12px; font-weight:600; white-space:nowrap;">
                {{ $s[1] }}
            </div>
            @if(!$loop->last)
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2.5" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            @endif
            @endforeach
            <span style="font-size:11px; color:#9ca3af; margin:0 4px;">atau</span>
            @foreach($cabang as $s)
            <div style="background:{{ $s[3] }}; color:{{ $s[2] }}; border-radius:9999px; padding:5px 14px; font-size:12px; font-weight:600; white-space:nowrap; border:1.5px dashed {{ $s[2] }}44;">
                {{ $s[1] }} → Stok
            </div>
            @endforeach
        </div>
        <p style="font-size:11px; color:#9ca3af; margin:8px 0 0;">Update status via: <span style="color:#374151;">Scan Barcode</span> · <span style="color:#374151;">Detail Gudang</span> · <span style="color:#374151;">Edit Produk</span></p>
    </div>

    {{-- ABSENSI HARI INI --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0;">Absensi Hari Ini</p>
            <span style="font-size:11px; color:#6b7280;">{{ now()->format('d M Y') }}</span>
        </div>
        <div style="display:flex; gap:12px; margin-bottom:12px;">
            <div style="flex:1; text-align:center; background:#dcfce7; border-radius:10px; padding:12px 8px;">
                <p style="font-size:24px; font-weight:700; color:#15803d; margin:0; line-height:1;">{{ $d['sudahAbsen'] }}</p>
                <p style="font-size:11px; color:#15803d; margin:3px 0 0;">Sudah Absen</p>
            </div>
            <div style="flex:1; text-align:center; background:{{ $d['belumAbsen'] > 0 ? '#fee2e2' : '#f3f4f6' }}; border-radius:10px; padding:12px 8px;">
                <p style="font-size:24px; font-weight:700; color:{{ $d['belumAbsen'] > 0 ? '#b91c1c' : '#374151' }}; margin:0; line-height:1;">{{ $d['belumAbsen'] }}</p>
                <p style="font-size:11px; color:{{ $d['belumAbsen'] > 0 ? '#b91c1c' : '#6b7280' }}; margin:3px 0 0;">Belum Absen</p>
            </div>
        </div>
        @if(count($d['belumAbsenList']) > 0)
        <div>
            <p style="font-size:11px; color:#9ca3af; margin:0 0 6px;">Belum clock in:</p>
            @foreach($d['belumAbsenList'] as $u)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f9fafb;">
                <span style="font-size:12px; color:#374151; font-weight:500;">{{ $u['name'] }}</span>
                <span style="font-size:10px; color:#9ca3af; background:#f3f4f6; border-radius:4px; padding:1px 6px;">{{ $u['role'] }}</span>
            </div>
            @endforeach
            @if($d['belumAbsen'] > count($d['belumAbsenList']))
            <p style="font-size:10px; color:#9ca3af; margin:6px 0 0;">+{{ $d['belumAbsen'] - count($d['belumAbsenList']) }} lainnya</p>
            @endif
        </div>
        @else
        <p style="font-size:12px; color:#15803d; font-weight:500; margin:0;">Semua karyawan sudah absen!</p>
        @endif
    </div>

    {{-- UNIT DIKIRIM --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0;">Unit Dalam Pengiriman</p>
            @if($d['totalDikirim'] > 0)
            <span style="background:#dbeafe; color:#1d4ed8; font-size:11px; font-weight:600; padding:2px 8px; border-radius:6px;">{{ $d['totalDikirim'] }} unit</span>
            @endif
        </div>
        @if($d['unitSampai'] > 0)
        <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:8px 12px; margin-bottom:10px;">
            <p style="font-size:12px; color:#92400e; margin:0; font-weight:500;">
                ⚠ {{ $d['unitSampai'] }} unit sudah <strong>Sampai</strong> — belum diselesaikan
            </p>
        </div>
        @endif
        @if(count($d['unitDikirim']) > 0)
        @foreach($d['unitDikirim'] as $u)
        <div style="padding:6px 0; border-bottom:1px solid #f9fafb;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:11px; font-family:monospace; font-weight:600; color:#111827;">{{ $u['code'] }}</span>
                <span style="font-size:10px; color:#9ca3af;">{{ $u['sejak'] }}</span>
            </div>
            <p style="font-size:11px; color:#6b7280; margin:1px 0 0;">{{ $u['produk'] }} · {{ $u['gudang'] }}</p>
        </div>
        @endforeach
        @if($d['totalDikirim'] > count($d['unitDikirim']))
        <p style="font-size:10px; color:#9ca3af; margin:6px 0 0;">+{{ $d['totalDikirim'] - count($d['unitDikirim']) }} unit lainnya</p>
        @endif
        @else
        <p style="font-size:13px; color:#9ca3af; margin:0;">Tidak ada unit sedang dikirim.</p>
        @endif
    </div>

    {{-- TUGAS DEADLINE --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0;">Tugas — Deadline 7 Hari</p>
            @if($d['totalDeadlineMingguIni'] > 0)
            <span style="background:#fee2e2; color:#b91c1c; font-size:11px; font-weight:600; padding:2px 8px; border-radius:6px;">{{ $d['totalDeadlineMingguIni'] }} tugas</span>
            @endif
        </div>
        @if(count($d['tugasDeadline']) > 0)
        @foreach($d['tugasDeadline'] as $t)
        <div style="padding:7px 0; border-bottom:1px solid #f9fafb; display:flex; align-items:center; justify-content:space-between; gap:8px;">
            <div style="min-width:0;">
                <p style="font-size:12px; font-weight:500; color:#111827; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $t['nama'] }}</p>
                <p style="font-size:11px; color:#6b7280; margin:1px 0 0;">{{ $t['assigned'] }}</p>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <span style="font-size:11px; font-weight:600; color:{{ $t['urgent'] ? '#b91c1c' : '#a16207' }}; background:{{ $t['urgent'] ? '#fee2e2' : '#fef9c3' }}; border-radius:6px; padding:2px 7px; white-space:nowrap;">
                    {{ $t['deadline'] }}
                </span>
                <p style="font-size:10px; color:#9ca3af; margin:2px 0 0;">{{ $t['sisa'] }} lagi</p>
            </div>
        </div>
        @endforeach
        @else
        <p style="font-size:13px; color:#9ca3af; margin:0;">Tidak ada tugas deadline minggu ini.</p>
        @endif
    </div>

    {{-- RINGKASAN TRANSAKSI HARI INI --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0 0 12px;">Transaksi Hari Ini</p>
        <div style="display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; align-items:center; justify-content:space-between; background:#dcfce7; border-radius:8px; padding:10px 14px;">
                <span style="font-size:13px; color:#15803d; font-weight:500;">Selesai</span>
                <span style="font-size:22px; font-weight:700; color:#15803d;">{{ $d['selesaiHariIni'] }}</span>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; background:{{ $d['dikembalikanHariIni'] > 0 ? '#fee2e2' : '#f9fafb' }}; border-radius:8px; padding:10px 14px;">
                <span style="font-size:13px; color:{{ $d['dikembalikanHariIni'] > 0 ? '#b91c1c' : '#6b7280' }}; font-weight:500;">Dikembalikan</span>
                <span style="font-size:22px; font-weight:700; color:{{ $d['dikembalikanHariIni'] > 0 ? '#b91c1c' : '#6b7280' }};">{{ $d['dikembalikanHariIni'] }}</span>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; background:#dbeafe; border-radius:8px; padding:10px 14px;">
                <span style="font-size:13px; color:#1d4ed8; font-weight:500;">Sedang Dikirim</span>
                <span style="font-size:22px; font-weight:700; color:#1d4ed8;">{{ $d['totalDikirim'] }}</span>
            </div>
        </div>
    </div>

</div>
