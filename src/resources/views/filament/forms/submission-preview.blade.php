@php
    $state = $getState() ?? [];
    $get   = fn($key) => data_get($state, $key);
@endphp

<div style="font-family:inherit;margin-bottom:1.5rem;">

    <style>
        .atelier-info-grid { display:grid; grid-template-columns:1fr; gap:10px; margin-bottom:12px; }
        .atelier-stat-grid { display:grid; grid-template-columns:1fr; gap:10px; margin-bottom:10px; }
        @media (min-width: 480px) {
            .atelier-info-grid { grid-template-columns:1fr 1fr; }
            .atelier-stat-grid { grid-template-columns:1fr 1fr 1fr; }
        }
    </style>

    {{-- Grid info --}}
    <div class="atelier-info-grid">
        <div style="background:#f9fafb;border-radius:10px;padding:10px 14px;">
            <div style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Peserta</div>
            <div style="font-size:13px;font-weight:600;color:#111827;">{{ $get('_peserta') }}</div>
        </div>
        <div style="background:#f9fafb;border-radius:10px;padding:10px 14px;">
            <div style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Waktu Submit</div>
            <div style="font-size:13px;font-weight:600;color:#111827;">{{ $get('_waktu_submit') }}</div>
        </div>
        <div style="background:#f9fafb;border-radius:10px;padding:10px 14px;">
            <div style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Jenis Pakaian</div>
            <div style="font-size:13px;font-weight:600;color:#111827;">{{ $get('_pakaian') }}</div>
        </div>
        <div style="background:#f9fafb;border-radius:10px;padding:10px 14px;">
            <div style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Jenis Pekerjaan</div>
            <div style="font-size:13px;font-weight:600;color:#111827;">{{ $get('_pekerjaan') }}</div>
        </div>
    </div>

    {{-- Instruksi --}}
    @if($get('_instruksi'))
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:10px 14px;margin-bottom:12px;">
        <div style="font-size:11px;color:#f97316;font-weight:600;margin-bottom:4px;">Instruksi Tugas</div>
        <div style="font-size:13px;color:#374151;white-space:pre-line;">{{ $get('_instruksi') }}</div>
    </div>
    @endif

    {{-- Hasil: 3 kartu --}}
    <div class="atelier-stat-grid">
        <div style="background:#eff6ff;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:11px;color:#93c5fd;margin-bottom:4px;">Target</div>
            <div style="font-size:20px;font-weight:700;color:#1d4ed8;">{{ $get('_target') }}</div>
        </div>
        <div style="background:#f0fdf4;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:11px;color:#86efac;margin-bottom:4px;">Selesai</div>
            <div style="font-size:20px;font-weight:700;color:#15803d;">{{ $get('_jumlah_selesai') }}</div>
        </div>
        <div style="background:#fff7ed;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:11px;color:#fdba74;margin-bottom:4px;">Poin Otomatis</div>
            <div style="font-size:20px;font-weight:700;color:#ea580c;">{{ number_format((float) $get('_poin_otomatis'), 2) }}</div>
        </div>
    </div>

    {{-- Formula --}}
    <div style="background:#f9fafb;border-radius:8px;padding:8px 14px;font-size:11px;color:#6b7280;margin-bottom:12px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
        <span>Kategori: <b style="color:#374151;">{{ $get('_kategori_nilai') }}</b></span>
        <span style="color:#d1d5db;">·</span>
        <span>Nilai Dasar: <b style="color:#374151;">{{ $get('_nilai_dasar') }}</b></span>
        <span style="color:#d1d5db;">·</span>
        <span>Bobot: <b style="color:#374151;">{{ $get('_bobot') }}</b></span>
        <span style="color:#d1d5db;">·</span>
        <span>Formula: <b style="color:#374151;">{{ $get('_nilai_dasar') }} × {{ $get('_bobot') }} × jumlah</b></span>
    </div>

    {{-- Catatan peserta --}}
    @if($get('_catatan_peserta') && $get('_catatan_peserta') !== '-')
    <div style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;margin-bottom:12px;">
        <div style="font-size:11px;color:#ca8a04;font-weight:600;margin-bottom:4px;">Catatan dari Peserta</div>
        <div style="font-size:13px;color:#374151;">{{ $get('_catatan_peserta') }}</div>
    </div>
    @endif

    {{-- Foto bukti --}}
    @if($get('_foto_url'))
    <div style="margin-bottom:12px;">
        <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Foto Bukti</div>
        <a href="{{ $get('_foto_url') }}" target="_blank">
            <img src="{{ $get('_foto_url') }}"
                 alt="Foto bukti"
                 style="width:100%;max-height:280px;object-fit:contain;border-radius:10px;border:1px solid #e5e7eb;background:#f9fafb;cursor:zoom-in;">
        </a>
        <div style="font-size:11px;color:#9ca3af;text-align:center;margin-top:4px;">Klik foto untuk buka ukuran penuh</div>
    </div>
    @else
    <div style="text-align:center;padding:16px;background:#f9fafb;border-radius:10px;font-size:13px;color:#9ca3af;margin-bottom:12px;">
        Tidak ada foto yang dilampirkan
    </div>
    @endif

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0 8px;">
    <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Penilaian Admin</div>
</div>
