<x-filament-panels::page>
    @php
        $user         = auth()->user();
        $isAdmin      = $user?->hasAnyRole(['super_admin', 'admin']);
        $isSuperAdmin = $user?->hasRole('super_admin');
        $sudahAbsen   = $isAdmin ? true : \App\Models\Attendance::whereDate('noted_at', now()->toDateString())
            ->where('user_id', $user?->id)
            ->where('type', 'clock_in')
            ->exists();
        $waNumber = \App\Models\Setting::get('katalog_wa_number', '6281234567890');
    @endphp

    @if(!$isAdmin && !$sudahAbsen)
    <div style="display:flex;align-items:flex-start;gap:0.75rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:0.75rem;padding:1rem;margin-bottom:1.25rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <div>
            <p style="font-size:0.875rem;font-weight:600;color:#92400e;margin-bottom:0.125rem;">Kamu belum absen masuk hari ini</p>
            <p style="font-size:0.75rem;color:#b45309;line-height:1.5;">Jangan lupa lakukan clock in di menu <strong>Absensi</strong> sebelum memulai pekerjaan.</p>
        </div>
    </div>
    @endif

    @if($isSuperAdmin)
    {{-- Pengaturan Katalog — hanya super_admin --}}
    <div wire:ignore style="background:#fff;border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:1.5rem;"
        x-data="{
            wa: '{{ $waNumber }}',
            saving: false,
            saved: false,
            error: '',
            save() {
                const raw = this.wa.replace(/\D/g, '');
                if (raw.length < 8 || raw.length > 15) { this.error = 'Nomor tidak valid (8–15 digit)'; return; }
                this.error = '';
                this.saving = true;
                fetch('/admin/setting/katalog-wa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                    body: JSON.stringify({ wa_number: raw })
                })
                .then(r => r.json())
                .then(d => {
                    this.saving = false;
                    if (d.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else { this.error = d.message || 'Gagal menyimpan'; }
                })
                .catch(() => { this.saving = false; this.error = 'Gagal menghubungi server'; });
            }
        }">
        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.875rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="color:#25d366;flex-shrink:0"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.55 4.107 1.514 5.833L.057 23.394a.5.5 0 00.549.549l5.56-1.457A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.882a9.866 9.866 0 01-5.031-1.377l-.36-.214-3.731.979.994-3.634-.234-.374A9.867 9.867 0 012.118 12C2.118 6.534 6.534 2.118 12 2.118c5.466 0 9.882 4.416 9.882 9.882 0 5.466-4.416 9.882-9.882 9.882z"/></svg>
            <span style="font-size:0.9375rem;font-weight:700;color:#111827;">Pengaturan Katalog Publik</span>
            <span style="font-size:0.7rem;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:100px;padding:2px 8px;font-weight:700;margin-left:4px;">Super Admin</span>
        </div>
        <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1rem;">Nomor WhatsApp yang digunakan untuk tombol checkout di halaman katalog <strong>example.com/produk</strong></p>
        <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;border:1.5px solid #d1d5db;border-radius:0.625rem;overflow:hidden;background:#f9fafb;flex:1;min-width:200px;max-width:320px;" :style="error ? 'border-color:#ef4444' : ''">
                <span style="padding:0 10px;font-size:13px;color:#6b7280;font-weight:600;white-space:nowrap;border-right:1px solid #e5e7eb;background:#f3f4f6;height:100%;display:flex;align-items:center;">+</span>
                <input type="tel" x-model="wa" placeholder="6281234567890"
                    style="flex:1;border:none;outline:none;padding:10px 12px;font-size:14px;font-weight:600;background:transparent;color:#111;min-width:0;font-family:monospace"
                    @keydown.enter="save()">
            </div>
            <button @click="save()" :disabled="saving"
                style="height:42px;padding:0 20px;border-radius:0.625rem;background:#16a34a;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;transition:background .15s"
                :style="saving ? 'opacity:.6;cursor:not-allowed' : ''">
                <svg x-show="!saving && !saved" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <svg x-show="saving" width="14" height="14" fill="none" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="3" opacity=".3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                <svg x-show="saved && !saving" width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span x-text="saving ? 'Menyimpan...' : saved ? 'Tersimpan!' : 'Simpan'"></span>
            </button>
        </div>
        <p x-show="error" x-text="error" style="font-size:12px;color:#ef4444;margin-top:6px;font-weight:600"></p>
        <p style="font-size:11px;color:#9ca3af;margin-top:8px;">Format: kode negara tanpa + (contoh: <code style="background:#f3f4f6;padding:1px 5px;border-radius:4px;">628123456789</code>)</p>
        <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
    </div>
    @endif

    @if($isAdmin)
        {{-- Admin: tampilkan widgets seperti biasa --}}
        <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :data="$this->getWidgetData()"
            :widgets="$this->getVisibleWidgets()"
        />
    @else
        {{-- Non-admin: halaman selamat datang --}}
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;text-align:center;padding:2rem;">
            <div style="width:80px;height:80px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;border:2px solid #fed7aa;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <h1 style="font-size:1.75rem;font-weight:700;color:#111827;margin-bottom:0.5rem;">
                Selamat Datang, {{ $user?->name }}!
            </h1>
            <p style="font-size:1rem;color:#6b7280;margin-bottom:0.25rem;">
                Kamu login sebagai <span style="font-weight:600;color:#f97316;">{{ ucfirst(str_replace('_', ' ', $user?->roles->first()?->name ?? '-')) }}</span>
            </p>
            <p style="font-size:0.875rem;color:#9ca3af;max-width:400px;line-height:1.6;">
                Gunakan menu di sidebar untuk mengakses fitur yang tersedia untukmu.
            </p>
        </div>
    @endif
</x-filament-panels::page>
