@php
    $publicUrl = url('/katalogpdf');
    $current   = $this->getCurrent();
@endphp
<x-filament-panels::page>

<style>
    .cp-grid { display: grid; gap: 1.25rem; grid-template-columns: 1fr; align-items: start; }
    @media (min-width: 1024px) { .cp-grid { grid-template-columns: 380px 1fr; } }
    .cp-card { background: #fff; border: 1px solid #e5e7eb; border-radius: .875rem; padding: 1.25rem; }
    .cp-label { font-size: .75rem; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .5rem; }
    .cp-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .55rem 1rem; border-radius: .6rem; font-size: .8125rem; font-weight: 600; cursor: pointer; border: 1.5px solid #e5e7eb; background: #fff; color: #374151; text-decoration: none; transition: background .15s, border-color .15s; }
    .cp-btn:hover { background: #f9fafb; border-color: #d1d5db; }
    .cp-btn.primary { background: #f97316; border-color: #f97316; color: #fff; }
    .cp-btn.primary:hover { background: #ea580c; border-color: #ea580c; }
    .cp-btn[disabled] { opacity: .45; cursor: not-allowed; }
    .cp-btn svg { width: 16px; height: 16px; }
    .cp-url { display: flex; align-items: center; gap: .5rem; padding: .55rem .75rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: .6rem; font-size: .8125rem; color: #111827; word-break: break-all; }
    .cp-input { width: 100%; padding: .6rem .8rem; border: 1.5px solid #e5e7eb; border-radius: .6rem; font-size: .875rem; outline: none; background: #fff; }
    .cp-input:focus { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,.12); }
    #qr-print-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1.5rem 1.25rem 1.25rem; text-align: center; max-width: 320px; margin: 0 auto; }
    #qr-print-card .brand { font-family: Georgia, 'Times New Roman', serif; font-size: 1.35rem; font-weight: 600; letter-spacing: .28em; color: #1A1F2E; text-transform: uppercase; }
    #qr-print-card .sub { font-size: .7rem; letter-spacing: .12em; text-transform: uppercase; color: #B85C3A; margin-top: .15rem; font-weight: 600; }
    #qr-print-card canvas { display: block; width: 100%; max-width: 260px; height: auto; margin: 1rem auto .75rem; }
    #qr-print-card .cap { font-size: .9rem; font-weight: 600; color: #1A1F2E; }
    #qr-print-card .cap small { display: block; font-weight: 400; color: #6b7280; font-size: .75rem; margin-top: .1rem; }
    #qr-print-card .url { margin-top: .6rem; font-size: .7rem; color: #6b7280; word-break: break-all; }
    @media print {
        body * { visibility: hidden !important; }
        #qr-print-card, #qr-print-card * { visibility: visible !important; }
        #qr-print-card { position: fixed; left: 50%; top: 32mm; transform: translateX(-50%); width: 110mm; max-width: none; border: 1px solid #ccc; box-shadow: none; }
        #qr-print-card canvas { max-width: 90mm; }
    }
</style>

<script>
window.catalogQr = function (url) {
    return {
        url: url, copied: false, qr: null, n: 0,
        loadLib() {
            return new Promise((resolve, reject) => {
                if (window.qrcode) return resolve();
                const s = document.createElement('script');
                s.src = '/js/qrcode.min.js';
                s.onload = resolve; s.onerror = reject;
                document.head.appendChild(s);
            });
        },
        async init() {
            try {
                await this.loadLib();
                const qr = window.qrcode(0, 'M');
                qr.addData(this.url); qr.make();
                this.qr = qr; this.n = qr.getModuleCount();
                this.draw(this.$refs.preview, 560);
            } catch (e) { console.error('QR gagal dibuat', e); }
        },
        draw(canvas, px) {
            const quiet = 4, total = this.n + quiet * 2, cell = Math.max(1, Math.floor(px / total)), size = cell * total;
            canvas.width = size; canvas.height = size;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, size, size);
            ctx.fillStyle = '#1A1F2E';
            for (let r = 0; r < this.n; r++) for (let c = 0; c < this.n; c++) {
                if (this.qr.isDark(r, c)) ctx.fillRect((c + quiet) * cell, (r + quiet) * cell, cell, cell);
            }
        },
        save(blob, name) {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob); a.download = name;
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 2000);
        },
        downloadPng() {
            if (!this.qr) return;
            const c = document.createElement('canvas');
            this.draw(c, 1600);
            c.toBlob(b => this.save(b, 'QR_Katalog_Atelier.png'), 'image/png');
        },
        downloadSvg() {
            if (!this.qr) return;
            const quiet = 4, total = this.n + quiet * 2;
            let d = '';
            for (let r = 0; r < this.n; r++) for (let c = 0; c < this.n; c++) {
                if (this.qr.isDark(r, c)) d += 'M' + (c + quiet) + ' ' + (r + quiet) + 'h1v1h-1z';
            }
            const svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + total + ' ' + total + '" shape-rendering="crispEdges">' +
                '<rect width="' + total + '" height="' + total + '" fill="#fff"/><path d="' + d + '" fill="#1A1F2E"/></svg>';
            this.save(new Blob([svg], { type: 'image/svg+xml' }), 'QR_Katalog_Atelier.svg');
        },
        async copy() {
            try { await navigator.clipboard.writeText(this.url); this.copied = true; setTimeout(() => this.copied = false, 1800); }
            catch (e) { window.prompt('Salin link:', this.url); }
        },
    };
};
</script>

<div class="cp-grid">

    {{-- ===== QR ===== --}}
    <div class="cp-card" wire:ignore x-data="catalogQr(@js($publicUrl))">
        <p class="cp-label">QR Code Katalog</p>
        <p style="font-size:.8rem;color:#6b7280;margin-bottom:1rem;line-height:1.5;">
            QR ini selalu mengarah ke alamat tetap di bawah. Isi PDF bisa diganti kapan saja tanpa perlu cetak ulang QR.
        </p>

        <div id="qr-print-card">
            <div class="brand">Atelier</div>
            <div class="sub">E-Catalog</div>
            <canvas x-ref="preview" aria-label="QR code katalog"></canvas>
            <div class="cap">Scan to view our e-catalog<small>Pindai untuk melihat katalog</small></div>
            <div class="url">example.com/katalogpdf</div>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1.1rem;justify-content:center;">
            <button type="button" class="cp-btn primary" @click="downloadPng()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 20h14"/></svg>
                PNG
            </button>
            <button type="button" class="cp-btn" @click="downloadSvg()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 20h14"/></svg>
                SVG
            </button>
            <button type="button" class="cp-btn" @click="window.print()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z"/></svg>
                Cetak
            </button>
        </div>
        <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:.6rem;">PNG 1600px &amp; SVG (vektor) tanpa kartu — cocok untuk desain cetak. Tombol Cetak mencetak kartu di atas.</p>

        <div style="margin-top:1.1rem;">
            <p class="cp-label">Alamat Tujuan QR</p>
            <div class="cp-url">
                <span style="flex:1;" x-text="url"></span>
                <button type="button" class="cp-btn" style="padding:.3rem .65rem;" @click="copy()"><span x-text="copied ? 'Tersalin' : 'Salin'"></span></button>
            </div>
            <a href="{{ url('/katalogpdf') }}" target="_blank" rel="noopener" class="cp-btn" style="margin-top:.6rem;width:100%;">Buka halaman katalog</a>
        </div>
    </div>

    {{-- ===== PDF ===== --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        <div class="cp-card">
            <p class="cp-label">File PDF Aktif</p>
            @if($current)
                <div style="display:flex;align-items:center;gap:.9rem;padding:.85rem 1rem;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:.75rem;">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="flex:none"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
                    <div style="min-width:0;">
                        <div style="font-weight:600;color:#14532d;font-size:.9rem;word-break:break-all;">{{ $current['name'] }}</div>
                        <div style="font-size:.75rem;color:#15803d;margin-top:.1rem;">
                            {{ number_format($current['size'] / 1048576, 2) }} MB
                            @if($current['updated']) · diperbarui {{ \Carbon\Carbon::createFromTimestamp($current['updated'])->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB @endif
                        </div>
                    </div>
                </div>
            @else
                <div style="padding:.9rem 1rem;border:1.5px dashed #fcd34d;background:#fffbeb;border-radius:.75rem;font-size:.85rem;color:#92400e;">
                    Belum ada PDF. Halaman publik menampilkan "katalog sedang diperbarui" sampai kamu upload file di bawah.
                </div>
            @endif
        </div>

        <div class="cp-card"
             x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true; progress = 0"
             x-on:livewire-upload-finish="uploading = false"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <p class="cp-label">{{ $current ? 'Ganti File PDF' : 'Upload File PDF' }}</p>
            <p style="font-size:.8rem;color:#6b7280;margin-bottom:.85rem;line-height:1.5;">
                Format PDF, maksimal 12 MB. Disarankan halaman portrait (mis. 9:16) agar tampil penuh di HP. Setelah disimpan, file lama otomatis terhapus dan QR yang sama langsung menampilkan isi baru.
            </p>

            <input type="file" wire:model="pdfFile" accept="application/pdf,.pdf" class="cp-input" style="padding:.5rem;">

            <div x-show="uploading" x-cloak style="margin-top:.7rem;">
                <div style="height:6px;background:#f3f4f6;border-radius:999px;overflow:hidden;">
                    <div :style="'width:' + progress + '%'" style="height:100%;background:#f97316;transition:width .2s;"></div>
                </div>
                <p style="font-size:.72rem;color:#6b7280;margin-top:.3rem;">Mengunggah… <span x-text="progress"></span>%</p>
            </div>

            @error('pdfFile')
                <p style="font-size:.8rem;color:#dc2626;margin-top:.6rem;">{{ $message }}</p>
            @enderror

            <div style="margin-top:.9rem;display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
                <button type="button" class="cp-btn primary" wire:click="savePdf" wire:loading.attr="disabled" wire:target="savePdf,pdfFile" @disabled(!$pdfFile)>
                    <span wire:loading.remove wire:target="savePdf">{{ $current ? 'Simpan & Ganti PDF' : 'Simpan PDF' }}</span>
                    <span wire:loading wire:target="savePdf">Menyimpan…</span>
                </button>
                @if($pdfFile)
                    <span style="font-size:.78rem;color:#16a34a;">File siap disimpan: {{ $pdfFile->getClientOriginalName() }}</span>
                @endif
            </div>
        </div>

        <div class="cp-card">
            <p class="cp-label">Judul Katalog</p>
            <p style="font-size:.8rem;color:#6b7280;margin-bottom:.7rem;">Tampil di bagian atas halaman katalog (di bawah nama Atelier).</p>
            <form wire:submit="saveTitle" style="display:flex;gap:.6rem;flex-wrap:wrap;">
                <input type="text" wire:model="catalogTitle" maxlength="60" class="cp-input" style="flex:1;min-width:200px;" placeholder="E-Catalog TEI 2026">
                <button type="submit" class="cp-btn">Simpan</button>
            </form>
            @error('catalogTitle')
                <p style="font-size:.8rem;color:#dc2626;margin-top:.5rem;">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

</x-filament-panels::page>
