<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Setting Absensi</x-slot>
        <x-slot name="description">Konfigurasi aktif saat ini — hanya Super Admin yang dapat mengubah</x-slot>
        <x-slot name="headerEnd">
            <x-filament::button
                href="{{ \App\Filament\Pages\AttendanceSettings::getUrl() }}"
                tag="a"
                color="primary"
                size="sm"
                icon="heroicon-o-cog-6-tooth">
                Edit Setting
            </x-filament::button>
        </x-slot>

        <style>
            .atelier-settings-grid { display:grid; grid-template-columns: 1fr; gap: 1rem; }
            @media (min-width: 480px) { .atelier-settings-grid { grid-template-columns: repeat(2, 1fr); } }
            @media (min-width: 768px) { .atelier-settings-grid { grid-template-columns: repeat(3, 1fr); } }
        </style>
        <div class="atelier-settings-grid">

            {{-- Jam Masuk --}}
            <x-filament::card>
                <p style="font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Jam Masuk</p>
                <p style="font-size:1.75rem; font-weight:700; color:#111827;">{{ $workStart }}</p>
                <x-filament::badge color="info" style="margin-top:0.5rem;">Toleransi {{ $lateTolerance }} menit</x-filament::badge>
            </x-filament::card>

            {{-- Jam Pulang --}}
            <x-filament::card>
                <p style="font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Jam Pulang</p>
                <p style="font-size:1.75rem; font-weight:700; color:#111827;">{{ $workEnd }}</p>
                <x-filament::badge color="gray" style="margin-top:0.5rem;">Batas akhir kerja</x-filament::badge>
            </x-filament::card>

            {{-- Radius --}}
            <x-filament::card>
                <p style="font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Radius Absen</p>
                <p style="font-size:1.75rem; font-weight:700; color:#111827;">{{ $radius }} <span style="font-size:1rem; font-weight:400; color:#9ca3af;">m</span></p>
                <x-filament::badge color="primary" style="margin-top:0.5rem;">Area valid</x-filament::badge>
            </x-filament::card>

            {{-- Toleransi --}}
            <x-filament::card>
                <p style="font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Toleransi Terlambat</p>
                <p style="font-size:1.75rem; font-weight:700; color:#111827;">{{ $lateTolerance }} <span style="font-size:1rem; font-weight:400; color:#9ca3af;">mnt</span></p>
                <x-filament::badge :color="$lateToleranceActive ? 'success' : 'gray'" style="margin-top:0.5rem;">
                    {{ $lateToleranceActive ? 'Aktif' : 'Nonaktif' }}
                </x-filament::badge>
            </x-filament::card>

            {{-- Luar Radius --}}
            <x-filament::card>
                <p style="font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Absen Luar Radius</p>
                <x-filament::badge :color="$allowOutside ? 'warning' : 'danger'" style="margin-top:0.25rem; font-size:1rem;">
                    {{ $allowOutside ? 'Diizinkan' : 'Diblokir' }}
                </x-filament::badge>
                <p style="font-size:0.75rem; color:#9ca3af; margin-top:0.5rem;">{{ $allowOutside ? 'Ditandai tidak valid' : 'Tidak bisa absen' }}</p>
            </x-filament::card>

            {{-- Modul --}}
            <x-filament::card>
                <p style="font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Modul Absensi</p>
                <x-filament::badge :color="$attendanceModule ? 'success' : 'danger'" style="margin-top:0.25rem; font-size:1rem;">
                    {{ $attendanceModule ? 'Aktif' : 'Nonaktif' }}
                </x-filament::badge>
                <p style="font-size:0.75rem; color:#9ca3af; margin-top:0.5rem;">Seluruh fitur absensi</p>
            </x-filament::card>

            {{-- Cooldown Tugas --}}
            <x-filament::card>
                <p style="font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Cooldown Tugas</p>
                <p style="font-size:1.75rem; font-weight:700; color:#111827;">{{ $workCooldown }} <span style="font-size:1rem; font-weight:400; color:#9ca3af;">mnt</span></p>
                <x-filament::badge :color="$workCooldown > 0 ? 'warning' : 'gray'" style="margin-top:0.5rem;">
                    {{ $workCooldown > 0 ? 'Aktif' : 'Nonaktif' }}
                </x-filament::badge>
            </x-filament::card>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
