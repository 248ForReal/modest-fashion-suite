<div style="max-width:640px; margin:0 auto;">

    {{-- Error flash --}}
    @if(session('error'))
    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
        <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <span style="font-size:13px; font-weight:600; color:#b91c1c;">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Panduan alur status --}}
    <div style="background:#fafafa; border:1px solid #e5e7eb; border-radius:10px; padding:12px 16px; margin-bottom:14px;">
        <p style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin:0 0 8px;">Alur Status Unit</p>
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:5px;">
            @foreach([['stok','#15803d','#dcfce7'],['dipesan','#a16207','#fef9c3'],['dikirim','#1d4ed8','#dbeafe'],['sampai','#c2410c','#ffedd5'],['selesai','#374151','#f3f4f6']] as $i => $s)
            <span style="background:{{ $s[2] }}; color:{{ $s[1] }}; border-radius:9999px; padding:3px 10px; font-size:11px; font-weight:600;">{{ ucfirst($s[0]) }}</span>
            @if($i < 4)<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>@endif
            @endforeach
            <span style="font-size:10px; color:#9ca3af; margin-left:4px;">· Dikembalikan → Stok</span>
        </div>
    </div>

    {{-- Input Scan --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:16px;">
        <p style="font-weight:600; font-size:15px; color:#111827; margin:0 0 12px;">Scan atau Ketik Kode Unit</p>
        <form wire:submit="scan" style="display:flex; gap:8px;">
            <input type="text" wire:model="cfg.scan_input"
                placeholder="Arahkan scanner ke barcode atau ketik kode unit..."
                autofocus
                style="flex:1; border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:13px; color:#111827; outline:none;" />
            <button type="submit"
                style="background:#ea580c; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap;">
                Cari
            </button>
        </form>
        <p style="font-size:11px; color:#9ca3af; margin:6px 0 0;">Gunakan scanner pistol barcode — otomatis submit saat scan.</p>
    </div>

    {{-- Sukses --}}
    @if($cfg['success'])
    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
        <svg style="width:18px;height:18px;color:#16a34a;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span style="font-size:13px; font-weight:600; color:#15803d;">{{ $cfg['success_msg'] ?: 'Berhasil disimpan!' }} · Siap scan unit berikutnya.</span>
    </div>
    @endif

    {{-- Info Unit --}}
    @if($unit)
    @php
        $panelTab     = $cfg['panel_tab'] ?? 'status';
        $aksi         = $cfg['aksi'] ?? '';
        $gudangTujuan = $cfg['gudang_tujuan'] ?? null;
        $badgeColors  = [
            'success' => ['bg'=>'#dcfce7','color'=>'#15803d'],
            'warning' => ['bg'=>'#fef9c3','color'=>'#a16207'],
            'info'    => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
            'danger'  => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
            'primary' => ['bg'=>'#ffedd5','color'=>'#c2410c'],
            'gray'    => ['bg'=>'#f3f4f6','color'=>'#374151'],
        ];
        $bc = $badgeColors[$unit['status_color']] ?? $badgeColors['gray'];
        $unitBisaPindah = in_array($unit['status'], ['stok', 'dikembalikan']);
    @endphp
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); overflow:hidden; margin-bottom:16px;">

        {{-- Header unit --}}
        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <p style="font-size:11px; color:#9ca3af; margin:0 0 2px;">Kode Unit</p>
                <p style="font-size:20px; font-weight:700; color:#111827; font-family:monospace; margin:0;">{{ $unit['unit_code'] }}</p>
            </div>
            <span style="background:{{ $bc['bg'] }}; color:{{ $bc['color'] }}; padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:600;">
                {{ $unit['status_label'] }}
            </span>
        </div>

        {{-- Detail produk --}}
        <div style="padding:14px 20px; display:grid; grid-template-columns:1fr 1fr; gap:10px; border-bottom:1px solid #f3f4f6;">
            <div>
                <p style="font-size:11px; color:#9ca3af; margin:0 0 2px;">Produk</p>
                <p style="font-size:13px; font-weight:500; color:#111827; margin:0;">{{ $unit['product_nama'] }}</p>
            </div>
            <div>
                <p style="font-size:11px; color:#9ca3af; margin:0 0 2px;">Kode Produk</p>
                <p style="font-size:13px; font-family:monospace; color:#374151; margin:0;">{{ $unit['product_code'] }}</p>
            </div>
            <div>
                <p style="font-size:11px; color:#9ca3af; margin:0 0 2px;">Gudang</p>
                <p style="font-size:13px; color:#374151; margin:0;">{{ $unit['gudang'] }}</p>
            </div>
            <div>
                <p style="font-size:11px; color:#9ca3af; margin:0 0 2px;">Posisi</p>
                <p style="font-size:13px; color:#374151; margin:0;">{{ $unit['posisi'] ?: '—' }}</p>
            </div>
        </div>

        {{-- Tab Bar --}}
        <div style="display:flex; border-bottom:1px solid #e5e7eb;">
            @foreach([['status','Update Status','#ea580c'],['posisi','Edit Posisi','#059669'],['pindah','Pindah Gudang','#d97706']] as $tab)
            <button wire:click="$set('cfg.panel_tab','{{ $tab[0] }}')"
                style="flex:1; padding:10px 8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s;
                    background:{{ $panelTab===$tab[0] ? '#fff' : '#f9fafb' }};
                    color:{{ $panelTab===$tab[0] ? $tab[2] : '#9ca3af' }};
                    border-bottom:2px solid {{ $panelTab===$tab[0] ? $tab[2] : 'transparent' }};">
                {{ $tab[1] }}
            </button>
            @endforeach
        </div>

        {{-- Tab: Update Status --}}
        @if($panelTab === 'status')
        <div style="padding:16px 20px;">
            @php $nextStatuses = $this->getNextStatuses(); @endphp
            @if(count($nextStatuses) > 0)
                <p style="font-size:12px; color:#6b7280; margin:0 0 10px;">Pilih status tujuan:</p>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:{{ $aksi ? '14px' : '0' }};">
                    @foreach($nextStatuses as $val => $label)
                    <button wire:click="$set('cfg.aksi','{{ $val }}')"
                        style="border-radius:8px; padding:7px 16px; font-size:13px; font-weight:600; cursor:pointer;
                            background:{{ $aksi===$val ? '#ea580c' : '#f9fafb' }};
                            color:{{ $aksi===$val ? '#fff' : '#374151' }};
                            border:1.5px solid {{ $aksi===$val ? '#ea580c' : '#d1d5db' }};">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
                @if($aksi)
                <div style="display:flex; flex-direction:column; gap:10px; margin-top:14px;">
                    <div>
                        <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Catatan <span style="color:#9ca3af;">(opsional)</span></label>
                        <input type="text" wire:model="cfg.catatan" placeholder="misal: dikirim via JNE REG"
                            style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; box-sizing:border-box;" />
                    </div>
                    <button wire:click="simpan"
                        style="width:100%; background:#ea580c; color:#fff; border:none; border-radius:8px; padding:10px; font-size:13px; font-weight:600; cursor:pointer;">
                        Simpan Status
                    </button>
                </div>
                @endif
            @else
                <p style="font-size:13px; color:#9ca3af; font-style:italic; margin:0;">Status ini sudah final — tidak ada aksi selanjutnya.</p>
            @endif
        </div>
        @endif

        {{-- Tab: Edit Posisi --}}
        @if($panelTab === 'posisi')
        <div style="padding:16px 20px; display:flex; flex-direction:column; gap:12px;">
            <div>
                <p style="font-size:12px; color:#6b7280; margin:0 0 4px;">Posisi sekarang</p>
                <p style="font-size:13px; font-weight:600; color:#111827; margin:0; background:#f3f4f6; padding:6px 10px; border-radius:6px; font-family:monospace;">
                    {{ $unit['posisi'] ?: '—' }}
                </p>
            </div>
            <div>
                <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Posisi baru <span style="color:#9ca3af;">(kosongkan untuk hapus)</span></label>
                <input type="text" wire:model="cfg.posisi_input"
                    placeholder="misal: Rak A-3, Slot B2..."
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:9px 10px; font-size:13px; box-sizing:border-box;" />
            </div>
            <button wire:click="simpanPosisi"
                style="width:100%; background:#059669; color:#fff; border:none; border-radius:8px; padding:10px; font-size:13px; font-weight:600; cursor:pointer;">
                Simpan Posisi
            </button>
        </div>
        @endif

        {{-- Tab: Pindah Gudang --}}
        @if($panelTab === 'pindah')
        <div style="padding:16px 20px; display:flex; flex-direction:column; gap:12px;">
            @if($unitBisaPindah)
                <div>
                    <p style="font-size:12px; color:#6b7280; margin:0 0 4px;">Gudang sekarang</p>
                    <p style="font-size:13px; font-weight:600; color:#111827; margin:0;">{{ $unit['gudang'] }}</p>
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Gudang tujuan <span style="color:#b91c1c;">*</span></label>
                    <select wire:model="cfg.gudang_tujuan"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff;">
                        <option value="">-- Pilih gudang tujuan --</option>
                        @foreach($this->getWarehouseOptions() as $id => $nama)
                            @if($id != $unit['warehouse_id'])
                            <option value="{{ $id }}" @selected($gudangTujuan==$id)>{{ $nama }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Catatan <span style="color:#9ca3af;">(opsional)</span></label>
                    <input type="text" wire:model="cfg.pindah_catatan" placeholder="alasan pemindahan..."
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:13px; box-sizing:border-box;" />
                </div>
                <button wire:click="simpanPindah"
                    style="width:100%; background:#d97706; color:#fff; border:none; border-radius:8px; padding:10px; font-size:13px; font-weight:600; cursor:pointer;">
                    Pindahkan Unit
                </button>
            @else
                <div style="background:#fef3c7; border:1px solid #fcd34d; border-radius:8px; padding:12px 14px; font-size:13px; color:#92400e;">
                    Unit dengan status <strong>{{ $unit['status_label'] }}</strong> tidak bisa dipindah gudang. Unit harus berstatus <strong>Stok</strong> atau <strong>Dikembalikan</strong>.
                </div>
            @endif
        </div>
        @endif

    </div>

    {{-- Riwayat --}}
    @php $logs = $this->getUnitLogs(); @endphp
    @if(count($logs) > 0)
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); overflow:hidden; margin-bottom:16px;">
        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
            <p style="font-size:13px; font-weight:600; color:#374151; margin:0;">Riwayat Pergerakan Unit</p>
        </div>
        @php
            $logColors = [
                'stok'         => '#16a34a',
                'dipesan'      => '#ca8a04',
                'dikirim'      => '#2563eb',
                'sampai'       => '#ea580c',
                'selesai'      => '#6b7280',
                'dikembalikan' => '#dc2626',
            ];
        @endphp
        @foreach($logs as $i => $log)
        <div style="display:flex; gap:12px; padding:12px 20px; {{ $i < count($logs)-1 ? 'border-bottom:1px solid #f9fafb;' : '' }}">
            <div style="flex-shrink:0; width:8px; height:8px; border-radius:50%; background:{{ $logColors[$log['ke_raw']] ?? '#9ca3af' }}; margin-top:5px;"></div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span style="font-size:12px; color:#9ca3af;">{{ $log['dari'] }}</span>
                    <span style="font-size:11px; color:#d1d5db;">→</span>
                    <span style="font-size:12px; font-weight:600; color:{{ $logColors[$log['ke_raw']] ?? '#374151' }};">{{ $log['ke'] }}</span>
                    @if($log['gudang'] !== '-')
                    <span style="font-size:11px; color:#6b7280; background:#f3f4f6; padding:1px 6px; border-radius:4px;">{{ $log['gudang'] }}</span>
                    @endif
                </div>
                <div style="display:flex; gap:10px; margin-top:2px; flex-wrap:wrap;">
                    <span style="font-size:11px; color:#9ca3af;">{{ $log['waktu'] }}</span>
                    <span style="font-size:11px; color:#6b7280;">oleh {{ $log['user'] }}</span>
                    @if($log['catatan'])
                    <span style="font-size:11px; color:#6b7280; font-style:italic;">"{{ $log['catatan'] }}"</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @endif

</div>
