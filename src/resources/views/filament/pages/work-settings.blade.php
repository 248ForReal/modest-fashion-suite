<x-filament-panels::page>

{{-- Tab Bar --}}
<div style="display:flex;gap:0;border-bottom:2px solid #f1f5f9;margin-bottom:1.5rem;">
    @foreach([
        'work'         => ['label' => 'Setting Pekerjaan', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        'admin_access' => ['label' => 'Akses Admin',       'icon' => 'M4 6h16M4 12h16M4 18h7'],
        'login_access'   => ['label' => 'Akses Login',       'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'],
        'user_modules'   => ['label' => 'Akses Modul User',  'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    ] as $tab => $info)
    <button wire:click="$set('activeTab', '{{ $tab }}')" type="button" style="
        padding:0.625rem 1.25rem;font-size:0.875rem;font-weight:500;
        border:none;background:none;cursor:pointer;display:flex;align-items:center;gap:0.5rem;
        border-bottom:2px solid {{ $activeTab === $tab ? '#f97316' : 'transparent' }};
        color:{{ $activeTab === $tab ? '#f97316' : '#6b7280' }};
        margin-bottom:-2px;transition:color 0.15s;
    ">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="{{ $info['icon'] }}"/>
        </svg>
        {{ $info['label'] }}
    </button>
    @endforeach
</div>

{{-- ===== TAB 1: SETTING PEKERJAAN ===== --}}
@if($activeTab === 'work')
<x-filament::section>
    <x-slot name="heading">Siapa yang Bisa Membuat Tugas?</x-slot>
    <x-slot name="description">
        Pilih role yang diizinkan membuat dan mengassign pekerjaan ke peserta. Super Admin &amp; Admin selalu punya akses ini.
    </x-slot>

    <form wire:submit="saveWork">
        <p style="font-size:0.75rem;font-weight:600;color:#374151;margin-bottom:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Role dengan Akses Tetap</p>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
            @foreach(['Super Admin', 'Admin'] as $locked)
            <div style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.4rem 0.875rem;border-radius:0.5rem;border:1px solid #bbf7d0;background:#f0fdf4;">
                <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;flex-shrink:0;"></span>
                <span style="font-size:0.875rem;font-weight:500;color:#15803d;">{{ $locked }}</span>
                <span style="font-size:0.7rem;font-weight:600;color:#16a34a;background:#dcfce7;padding:0.15rem 0.5rem;border-radius:0.25rem;">Tetap</span>
            </div>
            @endforeach
        </div>

        <hr style="border:none;border-top:1px solid #f1f5f9;margin-bottom:1.5rem;">

        <p style="font-size:0.75rem;font-weight:600;color:#374151;margin-bottom:0.25rem;text-transform:uppercase;letter-spacing:0.05em;">Role yang Dapat Dikonfigurasi</p>
        <p style="font-size:0.75rem;color:#9ca3af;margin-bottom:1rem;">Centang role yang ingin diberi kemampuan membuat tugas pekerjaan.</p>

        @php $labels = $this->getRoleLabels(); $roles = $this->getEditableRoles(); @endphp

        <div style="display:grid;grid-template-columns:1fr;gap:0.75rem;margin-bottom:1.5rem;" class="atelier-roles-grid">
            <style>.atelier-roles-grid { } @media(min-width:480px){.atelier-roles-grid{grid-template-columns:repeat(2,1fr);}}</style>
            @foreach($roles as $role)
            @php $label = $labels[$role] ?? ucfirst(str_replace('_', ' ', $role)); @endphp
            <label wire:key="role-{{ $role }}" style="
                display:flex;align-items:center;justify-content:space-between;
                gap:0.75rem;padding:0.75rem 1rem;border-radius:0.75rem;cursor:pointer;
                border:1.5px solid {{ $assignableRoles[$role] ? '#fed7aa' : '#e5e7eb' }};
                background:{{ $assignableRoles[$role] ? '#fff7ed' : '#ffffff' }};
            ">
                <div style="display:flex;align-items:center;gap:0.625rem;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:50%;flex-shrink:0;background:{{ $assignableRoles[$role] ? '#ffedd5' : '#f3f4f6' }};">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $assignableRoles[$role] ? '#f97316' : '#9ca3af' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <span style="font-size:0.875rem;font-weight:500;color:{{ $assignableRoles[$role] ? '#c2410c' : '#374151' }};">{{ $label }}</span>
                </div>
                <input type="checkbox" wire:model.live="assignableRoles.{{ $role }}" style="width:1rem;height:1rem;accent-color:#f97316;cursor:pointer;flex-shrink:0;">
            </label>
            @endforeach
        </div>

        <hr style="border:none;border-top:1px solid #f1f5f9;margin-bottom:1.5rem;">

        <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.75rem;padding:1rem;margin-bottom:1.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div>
                <p style="font-size:0.875rem;font-weight:600;color:#1d4ed8;margin-bottom:0.25rem;">Role Penerima Tugas</p>
                <p style="font-size:0.75rem;color:#2563eb;line-height:1.5;">
                    Role <strong>Magang</strong>, <strong>Siswa</strong>, dan <strong>Education</strong> adalah penerima tugas — mereka tidak bisa membuat tugas dan tidak akan muncul di daftar ini.
                </p>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;">
            <x-filament::button type="submit" color="primary">Simpan Setting</x-filament::button>
        </div>
    </form>
</x-filament::section>
{{-- Mode Mandiri --}}
<x-filament::section style="margin-top:1.5rem;">
    <x-slot name="heading">Mode Mandiri (Sementara)</x-slot>
    <x-slot name="description">
        Aktifkan agar siswa/magang bisa membuat tugas sendiri dari dashboard ketika tidak ada tugas yang diassign supervisor. Matikan kembali setelah tidak dibutuhkan.
    </x-slot>
    <form wire:submit="saveSelfAssignMode">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem;border-radius:0.75rem;border:1.5px solid {{ $selfAssignMode ? '#fed7aa' : '#e5e7eb' }};background:{{ $selfAssignMode ? '#fff7ed' : '#f9fafb' }};">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="display:flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:0.75rem;background:{{ $selfAssignMode ? '#ffedd5' : '#f3f4f6' }};">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $selfAssignMode ? '#f97316' : '#9ca3af' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                <div>
                    <p style="font-size:0.875rem;font-weight:600;color:{{ $selfAssignMode ? '#c2410c' : '#374151' }};">
                        Mode Mandiri {{ $selfAssignMode ? '— Aktif' : '— Nonaktif' }}
                    </p>
                    <p style="font-size:0.75rem;color:#6b7280;margin-top:0.125rem;">
                        Siswa/magang dapat membuat tugas sendiri dari dashboard
                    </p>
                </div>
            </div>
            <label style="position:relative;display:inline-block;width:3rem;height:1.625rem;flex-shrink:0;">
                <input type="checkbox" wire:model.live="selfAssignMode" style="opacity:0;width:0;height:0;position:absolute;">
                <span style="
                    position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;
                    background:{{ $selfAssignMode ? '#f97316' : '#d1d5db' }};
                    border-radius:9999px;transition:background 0.2s;
                ">
                    <span style="
                        position:absolute;content:'';height:1.25rem;width:1.25rem;
                        left:{{ $selfAssignMode ? 'calc(100% - 1.375rem)' : '0.1875rem' }};
                        bottom:0.1875rem;background:white;border-radius:50%;transition:left 0.2s;
                    "></span>
                </span>
            </label>
        </div>

        {{-- Batas tugas mandiri --}}
        <div style="margin-top:1rem;display:flex;align-items:center;justify-content:space-between;padding:0.875rem 1rem;border-radius:0.75rem;border:1.5px solid #e5e7eb;background:#f9fafb;">
            <div>
                <p style="font-size:0.875rem;font-weight:600;color:#374151;margin:0 0 0.125rem;">Batas Tugas Mandiri Aktif</p>
                <p style="font-size:0.75rem;color:#6b7280;margin:0;">Maks tugas mandiri yang boleh berjalan bersamaan per user</p>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;flex-shrink:0;">
                <input type="number" wire:model.live="selfAssignMaxTugas" min="1" max="10"
                    style="width:4rem;border:1.5px solid #d1d5db;border-radius:0.5rem;padding:0.375rem 0.5rem;font-size:0.875rem;font-weight:600;text-align:center;color:#111827;background:#fff;">
                <span style="font-size:0.75rem;color:#9ca3af;">tugas</span>
            </div>
        </div>

        @if($selfAssignMode)
        <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:0.75rem;padding:1rem;margin-top:1rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <p style="font-size:0.75rem;color:#92400e;line-height:1.5;">
                <strong>Mode Mandiri sedang aktif.</strong> Siswa/magang yang sudah absen masuk dan tidak punya tugas aktif akan melihat form buat tugas di dashboard mereka. Matikan segera setelah tidak diperlukan.
            </p>
        </div>
        @endif

        <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
            <x-filament::button type="submit" color="{{ $selfAssignMode ? 'warning' : 'gray' }}">Simpan</x-filament::button>
        </div>
    </form>
</x-filament::section>

{{-- Auto Sync Stok --}}
<x-filament::section style="margin-top:1.5rem;">
    <x-slot name="heading">Auto Sync Stok dari Tugas</x-slot>
    <x-slot name="description">
        Jika aktif, stok produk akan bertambah otomatis saat tugas pekerjaan yang terhubung ke produk disetujui (approved). Matikan jika ingin input stok manual.
    </x-slot>
    <form wire:submit="saveAutoSyncStok">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem;border-radius:0.75rem;border:1.5px solid {{ $autoSyncStok ? '#bbf7d0' : '#e5e7eb' }};background:{{ $autoSyncStok ? '#f0fdf4' : '#f9fafb' }};">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="display:flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:0.75rem;background:{{ $autoSyncStok ? '#dcfce7' : '#f3f4f6' }};">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $autoSyncStok ? '#16a34a' : '#9ca3af' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </div>
                <div>
                    <p style="font-size:0.875rem;font-weight:600;color:{{ $autoSyncStok ? '#15803d' : '#374151' }};">
                        Auto Sync Stok {{ $autoSyncStok ? '— Aktif' : '— Nonaktif' }}
                    </p>
                    <p style="font-size:0.75rem;color:#6b7280;margin-top:0.125rem;">
                        {{ $autoSyncStok ? 'Stok bertambah otomatis saat tugas approved' : 'Stok harus diinput manual di halaman Produk' }}
                    </p>
                </div>
            </div>
            <label style="position:relative;display:inline-block;width:3rem;height:1.625rem;flex-shrink:0;">
                <input type="checkbox" wire:model.live="autoSyncStok" style="opacity:0;width:0;height:0;position:absolute;">
                <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:{{ $autoSyncStok ? '#16a34a' : '#d1d5db' }};border-radius:9999px;transition:background 0.2s;">
                    <span style="position:absolute;height:1.25rem;width:1.25rem;left:{{ $autoSyncStok ? 'calc(100% - 1.375rem)' : '0.1875rem' }};bottom:0.1875rem;background:white;border-radius:50%;transition:left 0.2s;"></span>
                </span>
            </label>
        </div>

        @if(!$autoSyncStok)
        <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:0.75rem;padding:1rem;margin-top:1rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <p style="font-size:0.75rem;color:#92400e;line-height:1.5;">
                <strong>Auto Sync Stok nonaktif.</strong> Tugas yang approved tidak akan menambah stok produk secara otomatis. Admin harus input jumlah unit manual di halaman Produk.
            </p>
        </div>
        @endif

        <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
            <x-filament::button type="submit" color="{{ $autoSyncStok ? 'success' : 'gray' }}">Simpan</x-filament::button>
        </div>
    </form>
</x-filament::section>

{{-- Modul Penerimaan Distribusi --}}
<x-filament::section style="margin-top:1.5rem;">
    <x-slot name="heading">Modul Penerimaan Distribusi</x-slot>
    <x-slot name="description">
        Jika aktif, setiap distribusi akan berstatus "Menunggu Penerimaan" dan harus dikonfirmasi diterima oleh kepala gudang sebelum dianggap selesai. Saat nonaktif, distribusi langsung dianggap selesai.
    </x-slot>
    <form wire:submit="saveModulPenerimaan">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem;border-radius:0.75rem;border:1.5px solid {{ $modulPenerimaan ? '#bfdbfe' : '#e5e7eb' }};background:{{ $modulPenerimaan ? '#eff6ff' : '#f9fafb' }};">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="display:flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:0.75rem;background:{{ $modulPenerimaan ? '#dbeafe' : '#f3f4f6' }};">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $modulPenerimaan ? '#2563eb' : '#9ca3af' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4"/><path d="M21 12c0 1.2-.504 2.3-1.31 3.1C18.8 16.2 17 17 15 17H9c-2 0-3.8-.8-5.1-1.9A4.5 4.5 0 012 12c0-1.2.5-2.3 1.31-3.1C4.2 7.8 6 7 8 7c.7 0 1.4.1 2 .3"/>
                    </svg>
                </div>
                <div>
                    <p style="font-size:0.875rem;font-weight:600;color:{{ $modulPenerimaan ? '#1d4ed8' : '#374151' }};">
                        Modul Penerimaan {{ $modulPenerimaan ? '— Aktif' : '— Nonaktif' }}
                    </p>
                    <p style="font-size:0.75rem;color:#6b7280;margin-top:0.125rem;">
                        {{ $modulPenerimaan ? 'Distribusi perlu konfirmasi penerimaan oleh kepala gudang' : 'Distribusi langsung selesai tanpa konfirmasi penerimaan' }}
                    </p>
                </div>
            </div>
            <label style="position:relative;display:inline-block;width:3rem;height:1.625rem;flex-shrink:0;">
                <input type="checkbox" wire:model.live="modulPenerimaan" style="opacity:0;width:0;height:0;position:absolute;">
                <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:{{ $modulPenerimaan ? '#3b82f6' : '#d1d5db' }};border-radius:9999px;transition:background 0.2s;">
                    <span style="position:absolute;height:1.25rem;width:1.25rem;left:{{ $modulPenerimaan ? 'calc(100% - 1.375rem)' : '0.1875rem' }};bottom:0.1875rem;background:white;border-radius:50%;transition:left 0.2s;"></span>
                </span>
            </label>
        </div>

        @if($modulPenerimaan)
        <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.75rem;padding:1rem;margin-top:1rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p style="font-size:0.75rem;color:#1e40af;line-height:1.5;">
                <strong>Modul Penerimaan aktif.</strong> Setiap distribusi akan masuk ke antrian penerimaan. Kepala gudang atau admin perlu mengkonfirmasi bahwa barang sudah diterima di gudang tujuan.
            </p>
        </div>
        @else
        <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem;margin-top:1rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p style="font-size:0.75rem;color:#6b7280;line-height:1.5;">
                <strong>Modul Penerimaan nonaktif.</strong> Distribusi langsung dianggap selesai. Tidak ada alur konfirmasi penerimaan. Cocok untuk operasional internal satu lokasi.
            </p>
        </div>
        @endif

        <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
            <x-filament::button type="submit" color="{{ $modulPenerimaan ? 'primary' : 'gray' }}">Simpan</x-filament::button>
        </div>
    </form>
</x-filament::section>
@endif

{{-- ===== TAB 2: AKSES ADMIN ===== --}}
@if($activeTab === 'admin_access')
<x-filament::section>
    <x-slot name="heading">Akses Menu Admin per Role</x-slot>
    <x-slot name="description">
        Centang menu yang bisa diakses tiap role di sidebar admin. Super Admin &amp; Admin selalu bisa akses semua menu. Kalau semua dinonaktifkan, role hanya bisa melihat Dashboard.
    </x-slot>

    <form wire:submit="saveAdminAccess">
        @php
            $menus = \App\Filament\Pages\WorkSettings::getAdminMenus();
            $roles = $this->getConfigurableRoles();
            $labels = $this->getRoleLabels();
        @endphp

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:0.625rem 1rem;background:#f8fafc;border-bottom:2px solid #e5e7eb;color:#374151;font-weight:600;min-width:140px;">Menu</th>
                        @foreach($roles as $role)
                        <th style="text-align:center;padding:0.625rem 0.75rem;background:#f8fafc;border-bottom:2px solid #e5e7eb;color:#f97316;font-weight:600;white-space:nowrap;">
                            {{ $labels[$role] ?? ucfirst(str_replace('_',' ',$role)) }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($menus as $menuKey => $menuLabel)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:0.625rem 1rem;color:#374151;font-weight:500;">
                            @if($menuKey === 'dashboard')
                            <span style="display:inline-flex;align-items:center;gap:0.375rem;">
                                {{ $menuLabel }}
                                <span style="font-size:0.65rem;background:#dcfce7;color:#16a34a;padding:0.1rem 0.4rem;border-radius:0.25rem;font-weight:600;">Tetap</span>
                            </span>
                            @else
                            {{ $menuLabel }}
                            @endif
                        </td>
                        @foreach($roles as $role)
                        <td style="text-align:center;padding:0.625rem 0.75rem;">
                            @if($menuKey === 'dashboard')
                            <span style="color:#22c55e;">✓</span>
                            @else
                            <input type="checkbox"
                                wire:model.live="adminAccess.{{ $role }}.{{ $menuKey }}"
                                style="width:1rem;height:1rem;accent-color:#f97316;cursor:pointer;">
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:1.5rem;">
            <x-filament::button type="submit" color="primary">Simpan Akses Admin</x-filament::button>
        </div>
    </form>
</x-filament::section>
@endif

{{-- ===== TAB 3: AKSES LOGIN ===== --}}
@if($activeTab === 'login_access')
<x-filament::section>
    <x-slot name="heading">Akses Login per Role</x-slot>
    <x-slot name="description">
        Tentukan halaman login mana yang bisa digunakan tiap role. Super Admin &amp; Admin selalu bisa login di keduanya.
    </x-slot>

    <form wire:submit="saveLoginAccess">
        @php
            $allRoles = \Spatie\Permission\Models\Role::whereNotIn('name', ['super_admin', 'admin'])->orderBy('name')->pluck('name')->toArray();
            $labels = array_merge($this->getRoleLabels(), [
                'support' => 'Support', 'magang' => 'Magang', 'siswa' => 'Siswa',
                'education' => 'Education', 'member' => 'Member', 'guest' => 'Guest',
            ]);
        @endphp

        <div style="display:grid;grid-template-columns:1fr;gap:0.75rem;margin-bottom:1.5rem;" class="atelier-login-grid">
            <style>@media(min-width:480px){.atelier-login-grid{grid-template-columns:repeat(2,1fr);}}</style>
            @foreach($allRoles as $role)
            @php $label = $labels[$role] ?? ucfirst(str_replace('_',' ',$role)); @endphp
            <div style="border:1.5px solid #e5e7eb;border-radius:0.75rem;padding:1rem;">
                <p style="font-size:0.875rem;font-weight:600;color:#374151;margin-bottom:0.75rem;">{{ $label }}</p>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    @foreach(['user' => 'Halaman User (/login)', 'admin' => 'Halaman Admin (/admin/login)', 'both' => 'Keduanya'] as $value => $optLabel)
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.8rem;color:#6b7280;">
                        <input type="radio"
                            wire:model.live="loginAccess.{{ $role }}"
                            value="{{ $value }}"
                            style="accent-color:#f97316;cursor:pointer;">
                        {{ $optLabel }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#fefce8;border:1px solid #fde68a;border-radius:0.75rem;padding:1rem;margin-bottom:1.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <p style="font-size:0.75rem;color:#92400e;line-height:1.5;">
                Setting ini mengontrol halaman login mana yang bisa diakses. Role yang hanya bisa login di halaman User tidak akan bisa masuk ke <strong>/admin/login</strong> meski mengetahui URL-nya.
            </p>
        </div>

        <div style="display:flex;justify-content:flex-end;">
            <x-filament::button type="submit" color="primary">Simpan Akses Login</x-filament::button>
        </div>
    </form>
</x-filament::section>
@endif


{{-- ===== TAB 4: AKSES MODUL USER ===== --}}
@if($activeTab === 'user_modules')
<x-filament::section>
    <x-slot name="heading">Akses Modul User per Role</x-slot>
    <x-slot name="description">
        Centang modul yang bisa diakses tiap role di dashboard user. Super Admin &amp; Admin selalu punya akses penuh. Jika semua dinonaktifkan, role hanya bisa melihat halaman utama.
    </x-slot>

    <form wire:submit="saveUserModuleAccess">
        @php
            $userModules = \App\Filament\Pages\WorkSettings::getUserModules();
            $userRoles   = $this->getAllUserRoles();
            $roleLabels  = array_merge($this->getRoleLabels(), [
                'magang'    => 'Magang',
                'siswa'     => 'Siswa',
                'education' => 'Education',
                'member'    => 'Member',
                'guest'     => 'Guest',
                'support'   => 'Support',
            ]);

            $moduleIcons = [
                'absensi'         => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'rekap_absensi'   => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'tugas_pekerjaan' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'cetak_barcode'   => 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z',
                'scan_barcode'    => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
                'distribusi'      => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            ];
        @endphp

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:0.625rem 1rem;background:#f8fafc;border-bottom:2px solid #e5e7eb;color:#374151;font-weight:600;min-width:160px;">Modul</th>
                        <th style="text-align:center;padding:0.625rem 0.75rem;background:#f8fafc;border-bottom:2px solid #e5e7eb;color:#374151;font-weight:600;white-space:nowrap;">Aktif</th>
                        @foreach($userRoles as $role)
                        <th style="text-align:center;padding:0.625rem 0.75rem;background:#f8fafc;border-bottom:2px solid #e5e7eb;color:#f97316;font-weight:600;white-space:nowrap;">
                            {{ $roleLabels[$role] ?? ucfirst(str_replace('_',' ',$role)) }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($userModules as $moduleKey => $moduleLabel)
                    @php $isEnabled = $userModuleEnabled[$moduleKey] ?? false; @endphp
                    <tr style="border-bottom:1px solid #f1f5f9;background:{{ $isEnabled ? '#ffffff' : '#fafafa' }};">
                        <td style="padding:0.625rem 1rem;">
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $isEnabled ? '#f97316' : '#d1d5db' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                    <path d="{{ $moduleIcons[$moduleKey] ?? 'M4 6h16M4 12h16M4 18h16' }}"/>
                                </svg>
                                <span style="color:{{ $isEnabled ? '#374151' : '#9ca3af' }};font-weight:500;">{{ $moduleLabel }}</span>
                                @if(!$isEnabled)
                                <span style="font-size:0.65rem;background:#f3f4f6;color:#9ca3af;padding:0.1rem 0.4rem;border-radius:0.25rem;font-weight:600;">Nonaktif</span>
                                @endif
                            </div>
                        </td>
                        {{-- Toggle aktif/nonaktif --}}
                        <td style="text-align:center;padding:0.625rem 0.75rem;">
                            <label style="position:relative;display:inline-block;width:2.5rem;height:1.375rem;">
                                <input type="checkbox" wire:model.live="userModuleEnabled.{{ $moduleKey }}" style="opacity:0;width:0;height:0;position:absolute;">
                                <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:{{ $isEnabled ? '#f97316' : '#d1d5db' }};border-radius:9999px;transition:background 0.2s;">
                                    <span style="position:absolute;height:1rem;width:1rem;left:{{ $isEnabled ? 'calc(100% - 1.125rem)' : '0.1875rem' }};bottom:0.1875rem;background:white;border-radius:50%;transition:left 0.2s;"></span>
                                </span>
                            </label>
                        </td>
                        {{-- Checkbox per role — disabled jika modul nonaktif --}}
                        @foreach($userRoles as $role)
                        <td style="text-align:center;padding:0.625rem 0.75rem;">
                            <input type="checkbox"
                                wire:model.live="userModuleAccess.{{ $role }}.{{ $moduleKey }}"
                                {{ !$isEnabled ? 'disabled' : '' }}
                                style="width:1rem;height:1rem;accent-color:#f97316;cursor:{{ $isEnabled ? 'pointer' : 'not-allowed' }};opacity:{{ $isEnabled ? '1' : '0.35' }};">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.75rem;padding:1rem;margin-top:1.25rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p style="font-size:0.75rem;color:#1e40af;line-height:1.5;">
                Setting ini mengontrol modul mana yang tampil &amp; bisa diakses di dashboard user. Role yang tidak dicentang pada suatu modul tidak akan melihat menu tersebut di sidebar mereka.
            </p>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:1.5rem;">
            <x-filament::button type="submit" color="primary">Simpan Akses Modul User</x-filament::button>
        </div>
    </form>
</x-filament::section>
@endif

</x-filament-panels::page>
