<x-filament-panels::page>

    {{-- Filter --}}
    <x-filament::section>
        <div style="display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap;">
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.25rem;">Bulan</label>
                <select wire:model.live="bulan" style="border:1px solid #d1d5db; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem;">
                    @foreach($this->getBulanOptions() as $val => $label)
                        <option value="{{ $val }}" @selected($this->bulan === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.25rem;">Tahun</label>
                <select wire:model.live="tahun" style="border:1px solid #d1d5db; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem;">
                    @foreach($this->getTahunOptions() as $val)
                        <option value="{{ $val }}" @selected((string)$this->tahun === (string)$val)>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.25rem;">Role</label>
                <select wire:model.live="filterRole" style="border:1px solid #d1d5db; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; min-width:150px;">
                    <option value="">Semua Role</option>
                    @foreach($this->getRoleOptions() as $val => $label)
                        <option value="{{ $val }}" @selected($this->filterRole === $val)>{{ ucfirst(str_replace('_', ' ', $label)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <a href="{{ $this->getExportUrl() }}" target="_blank"
                    style="display:inline-flex; align-items:center; gap:0.5rem; background:#16a34a; color:white; font-size:0.875rem; font-weight:600; padding:0.5rem 1.25rem; border-radius:0.5rem; text-decoration:none; white-space:nowrap;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Excel
                </a>
            </div>
        </div>
    </x-filament::section>

    {{-- Tabel per tanggal --}}
    @php
        $recap    = $this->getRecap();
        $days     = $this->getDaysInMonth();
        $bulanNama = $this->getBulanOptions()[$this->bulan];
        $tahun    = $this->tahun;
        $bulan    = (int) $this->bulan;
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Rekap Absensi — {{ $bulanNama }} {{ $tahun }}
            @if($this->filterRole)
                <span style="font-size:0.75rem; font-weight:400; color:#6b7280; margin-left:0.5rem;">
                    · {{ ucfirst(str_replace('_', ' ', $this->filterRole)) }}
                </span>
            @endif
        </x-slot>

        @if(count($recap) === 0)
            <p style="text-align:center; color:#9ca3af; padding:2rem 0;">Tidak ada data absensi bulan ini.</p>
        @else
        <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
            <table style="border-collapse:collapse; font-size:0.75rem; min-width:max-content;">
                <thead>
                    {{-- Baris header: Nama | Role | 1 2 3 ... 31 | Hadir | Telat | Tidak Hadir --}}
                    <tr style="background:#f9fafb;">
                        <th style="position:sticky; left:0; background:#f9fafb; z-index:2; text-align:left; padding:8px 12px; border:1px solid #e5e7eb; min-width:160px; font-weight:600; color:#374151;">Nama</th>
                        <th style="position:sticky; left:160px; background:#f9fafb; z-index:2; text-align:left; padding:8px 10px; border:1px solid #e5e7eb; min-width:90px; font-weight:600; color:#374151;">Role</th>
                        @for($d = 1; $d <= $days; $d++)
                        @php $isSun = \Illuminate\Support\Carbon::create($tahun, $bulan, $d)->isSunday(); @endphp
                        <th style="text-align:center; padding:6px 4px; border:1px solid #e5e7eb; min-width:38px; font-weight:600; color:{{ $isSun ? '#ef4444' : '#374151' }}; background:{{ $isSun ? '#fef2f2' : '#f9fafb' }};">
                            {{ $d }}
                        </th>
                        @endfor
                        <th style="text-align:center; padding:8px 10px; border:1px solid #e5e7eb; min-width:52px; font-weight:700; color:#15803d; background:#f0fdf4;">Hadir</th>
                        <th style="text-align:center; padding:8px 10px; border:1px solid #e5e7eb; min-width:52px; font-weight:700; color:#b45309; background:#fffbeb;">Telat</th>
                        <th style="text-align:center; padding:8px 10px; border:1px solid #e5e7eb; min-width:64px; font-weight:700; color:#dc2626; background:#fef2f2;">Tdk Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recap as $row)
                    <tr style="border-bottom:1px solid #f3f4f6;" class="hover:bg-gray-50">
                        <td style="position:sticky; left:0; background:#fff; z-index:1; padding:8px 12px; border:1px solid #e5e7eb; font-weight:600; color:#111827; white-space:nowrap;">
                            {{ $row['name'] }}
                            @if(!empty($row['custom']))
                            <span style="display:block; font-size:0.65rem; font-weight:500; color:#b45309;">Jam masuk custom {{ $row['jam_masuk'] }}</span>
                            @endif
                        </td>
                        <td style="position:sticky; left:160px; background:#fff; z-index:1; padding:8px 10px; border:1px solid #e5e7eb; color:#6b7280; white-space:nowrap; font-size:0.7rem;">
                            {{ $row['roles'] ?: '-' }}
                        </td>
                        @foreach($row['per_tanggal'] as $day => $info)
                        @if($info['type'] === 'sunday')
                        <td style="text-align:center; padding:6px 4px; border:1px solid #e5e7eb; background:#fef2f2; color:#fca5a5; font-size:0.7rem;">—</td>
                        @elseif($info['type'] === 'hadir')
                        <td style="text-align:center; padding:6px 4px; border:1px solid #e5e7eb; background:{{ $info['telat'] ? '#fee2e2' : '#f0fdf4' }}; color:{{ $info['telat'] ? '#b91c1c' : '#15803d' }}; font-weight:600; font-size:0.7rem; white-space:nowrap;">
                            {{ $info['jam'] }}
                        </td>
                        @else
                        <td style="text-align:center; padding:6px 4px; border:1px solid #e5e7eb; color:#d1d5db; font-size:0.7rem;">·</td>
                        @endif
                        @endforeach
                        <td style="text-align:center; padding:8px 10px; border:1px solid #e5e7eb; font-weight:700; color:#15803d; background:#f0fdf4;">{{ $row['total_hadir'] }}</td>
                        <td style="text-align:center; padding:8px 10px; border:1px solid #e5e7eb; font-weight:700; color:{{ $row['total_telat'] > 0 ? '#b45309' : '#9ca3af' }}; background:{{ $row['total_telat'] > 0 ? '#fffbeb' : '#fafafa' }};">{{ $row['total_telat'] }}</td>
                        <td style="text-align:center; padding:8px 10px; border:1px solid #e5e7eb; font-weight:700; color:{{ $row['tidak_hadir'] > 0 ? '#dc2626' : '#9ca3af' }}; background:{{ $row['tidak_hadir'] > 0 ? '#fef2f2' : '#fafafa' }};">{{ $row['tidak_hadir'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Keterangan --}}
        <div style="margin-top:1rem; display:flex; flex-wrap:wrap; gap:1rem; font-size:0.75rem;">
            <div style="display:flex; align-items:center; gap:0.4rem;">
                <span style="display:inline-block; width:24px; height:16px; background:#f0fdf4; border:1px solid #86efac; border-radius:3px;"></span>
                <span style="color:#374151;">Hadir tepat waktu</span>
            </div>
            <div style="display:flex; align-items:center; gap:0.4rem;">
                <span style="display:inline-block; width:24px; height:16px; background:#fee2e2; border:1px solid #fca5a5; border-radius:3px;"></span>
                <span style="color:#374151;">Terlambat</span>
            </div>
            <div style="display:flex; align-items:center; gap:0.4rem;">
                <span style="display:inline-block; width:24px; height:16px; background:#fef2f2; border:1px solid #fecaca; border-radius:3px;"></span>
                <span style="color:#374151;">Minggu</span>
            </div>
            <div style="display:flex; align-items:center; gap:0.4rem;">
                <span style="display:inline-block; width:24px; height:16px; background:#fff; border:1px solid #e5e7eb; border-radius:3px;"></span>
                <span style="color:#374151;">Tidak hadir</span>
            </div>
        </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
