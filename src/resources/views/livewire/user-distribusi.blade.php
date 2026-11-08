@php
    $gudangOptions  = $this->getGudangOptions();
    $asalId         = $this->cfg['gudang_asal_id'] ? (int) $this->cfg['gudang_asal_id'] : null;
    $tujuanId       = $this->cfg['gudang_tujuan_id'] ? (int) $this->cfg['gudang_tujuan_id'] : null;
    $produkList     = $this->getProdukDiGudang();
    $preview        = $this->getPreview();
    $orderId        = $this->cfg['order_id'] ?? null;
    $confirmed      = $this->cfg['confirmed'] ?? false;
    $jumlahMap      = $this->cfg['jumlah'] ?? [];
    $adaYangDiisi   = collect($jumlahMap)->filter(fn($v) => (int)$v > 0)->isNotEmpty();
@endphp

<div style="max-width:640px; margin:0 auto;">

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="background:#dcfce7; border:1px solid #86efac; border-radius:10px; padding:14px 16px; margin-bottom:14px; color:#15803d; font-size:13px; font-weight:500; display:flex; align-items:center; gap:10px;">
        <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('warning'))
    <div style="background:#fef9c3; border:1px solid #fde68a; border-radius:10px; padding:14px 16px; margin-bottom:14px; color:#92400e; font-size:13px;">
        <strong>Perhatian:</strong> {{ session('warning') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:10px; padding:14px 16px; margin-bottom:14px; color:#b91c1c; font-size:13px;">
        {{ session('error') }}
    </div>
    @endif

    {{-- Upload foto setelah proses berhasil --}}
    @if($orderId)
    <div style="background:#fff; border:2px solid #ea580c; border-radius:12px; padding:20px; margin-bottom:16px;">
        <p style="font-weight:700; font-size:15px; color:#111827; margin:0 0 4px;">Distribusi Berhasil!</p>
        <p style="font-size:13px; color:#6b7280; margin:0 0 16px;">Upload foto bukti pengiriman (opsional).</p>

        <form wire:submit.prevent="uploadFotoBukti">
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:6px;">Foto Bukti</label>
                <input type="file" wire:model="foto_bukti_file" accept="image/*"
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; box-sizing:border-box; background:#f9fafb;" />
                @error('foto_bukti_file') <span style="font-size:11px; color:#dc2626;">{{ $message }}</span> @enderror
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" style="flex:1; background:#ea580c; color:#fff; border:none; border-radius:8px; padding:12px; font-size:14px; font-weight:600; cursor:pointer;">
                    Upload Foto
                </button>
                <button type="button" wire:click="skipFoto" style="flex:1; background:#f3f4f6; color:#374151; border:none; border-radius:8px; padding:12px; font-size:14px; font-weight:500; cursor:pointer;">
                    Lewati
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Form Distribusi --}}
    @if(!$orderId)
    {{-- Step 1: Pilih Gudang --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:16px;">
        <p style="font-weight:600; font-size:15px; color:#111827; margin:0 0 16px;">Pilih Gudang</p>

        <div style="margin-bottom:12px;">
            <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Gudang Asal</label>
            <select wire:model.live="cfg.gudang_asal_id" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; color:#111827; background:#fff; box-sizing:border-box;">
                <option value="">-- Pilih Gudang Asal --</option>
                @foreach($gudangOptions as $id => $nama)
                    <option value="{{ $id }}" @selected($asalId == $id)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px;">Gudang Tujuan</label>
            <select wire:model.live="cfg.gudang_tujuan_id" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; color:#111827; background:#fff; box-sizing:border-box;">
                <option value="">-- Pilih Gudang Tujuan --</option>
                @foreach($gudangOptions as $id => $nama)
                    @if($id != $asalId)
                    <option value="{{ $id }}" @selected($tujuanId == $id)>{{ $nama }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    {{-- Step 2: Produk di Gudang Asal --}}
    @if($asalId && count($produkList) > 0)
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:16px;">
        <p style="font-weight:600; font-size:15px; color:#111827; margin:0 0 4px;">Pilih Produk & Jumlah</p>
        <p style="font-size:12px; color:#6b7280; margin:0 0 16px;">Isi jumlah unit yang ingin dipindahkan (kosongkan = tidak dipindahkan).</p>

        @foreach($produkList as $produk)
        @php
            $pid    = $produk['product_id'];
            $stok   = $produk['stok_di_asal'];
            $jml    = (int) ($jumlahMap[$pid] ?? 0);
            $diisi  = $jml > 0;
        @endphp
        <div style="border:1px solid {{ $diisi ? '#fed7aa' : '#f3f4f6' }}; border-radius:10px; padding:14px; margin-bottom:10px; background:{{ $diisi ? '#fff7ed' : '#fafafa' }};">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                <div>
                    <span style="font-size:13px; font-weight:600; color:#111827;">{{ $produk['nama'] }}</span>
                    <br>
                    <span style="font-size:11px; color:#6b7280;">{{ $produk['product_code'] }} · {{ $produk['kategori'] }}</span>
                </div>
                <span style="font-size:12px; font-weight:600; color:#15803d; background:#dcfce7; padding:3px 8px; border-radius:20px; white-space:nowrap;">{{ $stok }} unit</span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <div>
                    <label style="display:block; font-size:11px; color:#374151; margin-bottom:3px;">Jumlah</label>
                    <input type="number" wire:model.live="cfg.jumlah.{{ $pid }}"
                        min="0" max="{{ $stok }}" placeholder="0"
                        style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px; font-size:13px; box-sizing:border-box; @if($jml > $stok) border-color:#ef4444; background:#fee2e2; @endif" />
                    @if($jml > $stok)
                    <span style="font-size:11px; color:#dc2626;">Melebihi stok</span>
                    @endif
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:#374151; margin-bottom:3px;">Posisi Tujuan</label>
                    <input type="text" wire:model.live="cfg.posisi.{{ $pid }}"
                        placeholder="Rak A1 (opsional)"
                        style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px; font-size:13px; box-sizing:border-box;" />
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @elseif($asalId && count($produkList) === 0)
    <div style="background:#f9fafb; border:1px dashed #d1d5db; border-radius:12px; padding:24px; text-align:center; margin-bottom:16px;">
        <p style="font-size:13px; color:#6b7280; margin:0;">Tidak ada produk dengan status <strong>stok</strong> di gudang ini.</p>
    </div>
    @endif

    {{-- Step 3: Catatan + Preview + Konfirmasi --}}
    @if($asalId && $tujuanId && $adaYangDiisi)
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:16px;">
        <p style="font-weight:600; font-size:15px; color:#111827; margin:0 0 12px;">Catatan (Opsional)</p>
        <textarea wire:model.live="cfg.catatan" rows="2" placeholder="Catatan distribusi..."
            style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:13px; box-sizing:border-box; resize:none;"></textarea>
    </div>

    {{-- Preview --}}
    @php $prevData = $preview; @endphp
    @if(count($prevData) > 0)
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:16px;">
        <p style="font-weight:600; font-size:14px; color:#111827; margin:0 0 12px;">Ringkasan Distribusi</p>

        @php
            $asalNama   = $gudangOptions[$asalId] ?? '-';
            $tujuanNama = $gudangOptions[$tujuanId] ?? '-';
            $totalUnit  = collect($prevData)->sum('jumlah');
        @endphp
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; font-size:13px; color:#1d4ed8; margin-bottom:12px;">
            <strong>{{ $asalNama }}</strong> → <strong>{{ $tujuanNama }}</strong>
            <span style="float:right; font-weight:700;">{{ $totalUnit }} unit</span>
        </div>

        @foreach($prevData as $item)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #f3f4f6;">
            <div>
                <span style="font-size:13px; color:#111827;">{{ $item['nama'] }}</span>
                @if($item['posisi_tujuan'])
                <span style="font-size:11px; color:#6b7280;"> → {{ $item['posisi_tujuan'] }}</span>
                @endif
                @if(!$item['cukup'])
                <br><span style="font-size:11px; color:#dc2626;">Stok terbatas: tersedia {{ $item['stok'] }}</span>
                @endif
            </div>
            <span style="font-size:13px; font-weight:700; color:{{ $item['cukup'] ? '#15803d' : '#d97706' }};">{{ $item['jumlah'] }} unit</span>
        </div>
        @endforeach

        {{-- Konfirmasi checkbox --}}
        <div style="margin-top:14px; display:flex; align-items:center; gap:10px;">
            <input type="checkbox" wire:model.live="cfg.confirmed" id="konfirmasi"
                style="width:18px; height:18px; accent-color:#ea580c; cursor:pointer;" />
            <label for="konfirmasi" style="font-size:13px; color:#374151; cursor:pointer;">
                Saya konfirmasi data distribusi ini sudah benar
            </label>
        </div>
    </div>

    <button wire:click="prosesDistribusi" @if(!$confirmed) disabled @endif
        wire:loading.attr="disabled" wire:loading.class="opacity-60"
        style="width:100%; background:{{ $confirmed ? '#ea580c' : '#d1d5db' }}; color:#fff; border:none; border-radius:10px; padding:14px; font-size:15px; font-weight:700; cursor:{{ $confirmed ? 'pointer' : 'not-allowed' }}; margin-bottom:8px;">
        <span wire:loading.remove wire:target="prosesDistribusi">Proses Distribusi</span>
        <span wire:loading wire:target="prosesDistribusi">Memproses...</span>
    </button>

    <button wire:click="resetForm" style="width:100%; background:transparent; color:#6b7280; border:1px solid #d1d5db; border-radius:10px; padding:11px; font-size:14px; cursor:pointer;">
        Reset Form
    </button>
    @endif
    @endif
    @endif

</div>
