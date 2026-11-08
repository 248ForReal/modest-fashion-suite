<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Rekap Absensi</x-slot>
        <x-slot name="headerEnd">
            <a href="{{ $this->getExportUrl() }}" target="_blank"
                style="display:inline-flex; align-items:center; gap:0.4rem; background:#22c55e; color:white; font-size:0.8rem; font-weight:600; padding:0.4rem 0.85rem; border-radius:0.5rem; text-decoration:none;">
                ↓ Export Rekap
            </a>
        </x-slot>

        {{-- Filter --}}
        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.25rem;">Bulan</label>
                <select wire:model.live="bulan" style="border:1px solid #d1d5db; border-radius:0.5rem; padding:0.4rem 0.75rem; font-size:0.875rem;">
                    @foreach($this->getBulanOptions() as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.25rem;">Tahun</label>
                <select wire:model.live="tahun" style="border:1px solid #d1d5db; border-radius:0.5rem; padding:0.4rem 0.75rem; font-size:0.875rem;">
                    @foreach($this->getTahunOptions() as $val)
                        <option value="{{ $val }}">{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php $recap = $this->getRecap(); @endphp

        @if(count($recap) === 0)
            <p style="text-align:center; color:#9ca3af; padding:2rem 0; font-size:0.875rem;">Tidak ada data absensi bulan ini.</p>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%; font-size:0.875rem; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="text-align:left; padding:0.65rem 1rem; font-weight:600; color:#374151;">Nama</th>
                        <th style="text-align:left; padding:0.65rem 1rem; font-weight:600; color:#374151;">Role</th>
                        <th style="text-align:center; padding:0.65rem 0.75rem; font-weight:600; color:#374151;">Hadir</th>
                        <th style="text-align:center; padding:0.65rem 0.75rem; font-weight:600; color:#22c55e;">Tepat</th>
                        <th style="text-align:center; padding:0.65rem 0.75rem; font-weight:600; color:#ef4444;">Terlambat</th>
                        <th style="text-align:center; padding:0.65rem 0.75rem; font-weight:600; color:#8b5cf6;">Izin</th>
                        <th style="text-align:center; padding:0.65rem 0.75rem; font-weight:600; color:#f59e0b;">Cuti</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recap as $row)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:0.65rem 1rem;">
                            <p style="font-weight:600; color:#111827; margin:0;">{{ $row['name'] }}</p>
                            <p style="font-size:0.75rem; color:#9ca3af; margin:0;">{{ $row['email'] }}</p>
                        </td>
                        <td style="padding:0.65rem 1rem; color:#6b7280;">{{ $row['roles'] ?: '-' }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:center; font-weight:700; color:#111827;">{{ $row['hadir'] }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:center; font-weight:700; color:#22c55e;">{{ $row['tepat'] }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:center; font-weight:700; color:#ef4444;">{{ $row['terlambat'] }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:center; font-weight:700; color:#8b5cf6;">{{ $row['izin'] }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:center; font-weight:700; color:#f59e0b;">{{ $row['cuti'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
