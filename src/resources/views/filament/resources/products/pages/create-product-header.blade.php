@php
    $prompt = <<<'PROMPT'
You are a product data assistant for Atelier, an Indonesian modest fashion & ethnic clothing brand.

Task: analyze the product photo I send, then fill in EXACTLY the format below.
Do not add extra fields. Do not change the format.
When unsure, choose the closest option from the provided list.
Write ALL text in English — product name, description, notes, color names, everything.
Only the KATEGORI and JENIS PAKAIAN values must be copied exactly as listed (they are fixed Indonesian labels).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
NAMA PRODUK: [short & catchy English product name, max 60 characters. Mention motif/main material if clearly visible]

KATEGORI: [choose ONE — copy exactly as written]
Pilihan: Kasual | Formal | Khusus | Olahraga | Seragam | Mukena | Anak & Bayi | Aksesoris

JENIS PAKAIAN: [choose ONE from this list — copy EXACTLY including commas and slashes]

• Kasual       → Kaos polos | Casual Ethnic | Kemeja santai | Tunik panjang | Blus batik longgar | Hoodie | Cardigan | Outer tenun/kimono etnik | Jeans | Celana pendek | Celana palazzo/longgar | Rok batik casual
• Formal       → Kemeja kerja | Blazer | Jas | Gamis kerja/muslim | Dress batik formal | Jas formal | Rok pensil
• Khusus       → Gaun pengantin | Abaya, kaftan | Maxi dress tribal/tenun | Bra | Daster, piyama
• Olahraga     → Jersey | Training pants
• Seragam      → Seragam pramuka | Seragam kantor
• Mukena       → Mukena polos dua setelan | Mukena bordir klasik, bordir mesin | Mukena ringkas bahan ringan | Mukena anak motif lucu | Mukena tenun/batik/songket/jumputan
• Anak & Bayi  → Kaos anak | Blus etnik anak | Dress anak | Dress etnik anak
• Aksesoris    → Topi | Hijab, scarf

WARNA DOMINAN: [3 most dominant colors — each: English color name + HEX code.
Format: Name #HEXCODE | Name #HEXCODE | Name #HEXCODE
Example: Dark Brown #3B1C0C | Brick Red #A0302A | Charcoal #2D2D2D]

DESKRIPSI: [2–3 sentences in English, max 300 characters. Write a compelling e-commerce description: highlight the unique motif/material, the occasion it suits, and why customers will love it. Confident, natural tone — no generic filler phrases like "this product is" or "high quality".]

HARGA (Rp): [leave blank — owner fills manually]

JUMLAH TOTAL: [estimate number of items visible in photo. If unclear, write: 0]

STATUS: aktif
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

After the format above, add:
CATATAN: [in English — things the owner needs to confirm: material, sizing, or details not clearly visible in the photo]
PROMPT;
@endphp

<style>
/* ── Mode toggle ── */
#atelier-create-wrapper { margin-bottom: 0; }
.ai-mode-toggle { display: flex; gap: 8px; margin-bottom: 20px; }
.ai-mode-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 20px; border-radius: 8px; font-size: 14px; font-weight: 500;
    border: 1.5px solid #e5e7eb; background: #fff; color: #6b7280;
    cursor: pointer; transition: all .15s;
}
.ai-mode-btn:hover { border-color: #ea580c; color: #ea580c; }
.ai-mode-btn.active, .ai-mode-btn[data-active="true"] { background: #ea580c; border-color: #ea580c; color: #fff; }

/* ── Wizard ── */
.ai-wizard { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
.ai-steps { display: flex; border-bottom: 1px solid #f3f4f6; background: #fafafa; padding: 0 20px; flex-wrap: wrap; }
.ai-step-item {
    display: flex; align-items: center; gap: 8px;
    padding: 14px 16px; font-size: 13px; color: #9ca3af;
    border-bottom: 2px solid transparent; user-select: none; transition: color .15s;
}
.ai-step-item.active { color: #ea580c; border-bottom-color: #ea580c; }
.ai-step-item.done { color: #16a34a; cursor: pointer; }
.ai-step-item.done:hover { color: #15803d; }
.ai-step-num {
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; background: #f3f4f6; color: #9ca3af; flex-shrink: 0;
}
.ai-step-item.active .ai-step-num { background: #ea580c; color: #fff; }
.ai-step-item.done .ai-step-num { background: #16a34a; color: #fff; }
.ai-body { padding: 24px; }

/* Step 1 */
.prompt-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
.copy-prompt-btn {
    display: flex; align-items: center; gap: 6px; padding: 7px 16px;
    background: #ea580c; color: #fff; border: none; border-radius: 7px;
    font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s;
}
.copy-prompt-btn:hover { background: #c2410c; }
.copy-prompt-btn.copied { background: #16a34a; }
.prompt-box {
    background: #1e2235; border-radius: 8px; padding: 18px; font-size: 12.5px;
    line-height: 1.7; color: #e8e2d8; white-space: pre-wrap; word-break: break-word;
    max-height: 300px; overflow-y: auto; font-family: inherit;
}
.how-steps { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
.how-step { display: flex; gap: 12px; align-items: flex-start; font-size: 13px; color: #374151; line-height: 1.55; }
.how-num {
    width: 22px; height: 22px; border-radius: 50%; background: #ea580c; color: #fff;
    font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
}

/* Buttons */
.ai-next-btn {
    margin-top: 20px; padding: 10px 28px; background: #ea580c; color: #fff;
    border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: background .15s;
}
.ai-next-btn:hover { background: #c2410c; }
.ai-next-btn:disabled { background: #d1d5db; cursor: not-allowed; }
.ai-back-btn {
    margin-top: 20px; margin-right: 10px; padding: 10px 20px; background: #fff; color: #6b7280;
    border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-weight: 500;
    cursor: pointer; transition: all .15s;
}
.ai-back-btn:hover { border-color: #9ca3af; color: #374151; }

/* Step 2 */
.ai-textarea {
    width: 100%; min-height: 240px; padding: 14px; border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: 13px; line-height: 1.65; color: #111827; resize: vertical; outline: none;
    font-family: inherit; box-sizing: border-box; transition: border-color .15s;
}
.ai-textarea:focus { border-color: #ea580c; box-shadow: 0 0 0 3px rgba(234,88,12,.08); }
.ai-parse-error { margin-top: 10px; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 7px; font-size: 13px; color: #b91c1c; }

/* Step 3 */
.preview-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 640px) { .preview-grid { grid-template-columns: 1fr; } }
.preview-field { display: flex; flex-direction: column; gap: 5px; }
.preview-field.full { grid-column: 1 / -1; }
.preview-label { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #6b7280; }
.preview-val { padding: 9px 12px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 7px; font-size: 13px; color: #111827; min-height: 38px; }
.preview-val.ai-filled { border-color: #a7f3d0; background: #f0fdf4; color: #065f46; }
.preview-val.empty { color: #9ca3af; font-style: italic; border-style: dashed; }
.preview-input {
    padding: 9px 12px; border: 1.5px solid #e5e7eb; border-radius: 7px;
    font-size: 13px; color: #111827; outline: none; font-family: inherit; width: 100%; box-sizing: border-box;
}
.preview-input:focus { border-color: #ea580c; box-shadow: 0 0 0 3px rgba(234,88,12,.08); }
.preview-select {
    padding: 9px 12px; border: 1.5px solid #e5e7eb; border-radius: 7px;
    font-size: 13px; color: #111827; outline: none; font-family: inherit; width: 100%;
    background: #fff; cursor: pointer; box-sizing: border-box;
}
.preview-select:focus { border-color: #ea580c; }
.warna-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.warna-chip { display: flex; align-items: center; gap: 6px; padding: 4px 10px 4px 6px; border-radius: 100px; border: 1.5px solid #e5e7eb; background: #f9fafb; font-size: 12px; color: #374151; }
.warna-dot { width: 14px; height: 14px; border-radius: 50%; border: 1px solid rgba(0,0,0,.1); flex-shrink: 0; }
.size-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.size-chip { padding: 4px 12px; border-radius: 6px; background: #f3f4f6; font-size: 12px; font-weight: 600; color: #374151; }
.catatan-box { padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 7px; font-size: 13px; color: #92400e; line-height: 1.6; }
.ai-badge { display: inline-flex; align-items: center; font-size: 10px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 100px; padding: 2px 7px; margin-left: 6px; }
.submit-ai-btn {
    flex: 1; padding: 11px; background: #ea580c; color: #fff;
    border: none; border-radius: 9px; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: background .15s; margin-top: 20px;
}
.submit-ai-btn:hover { background: #c2410c; }
.submit-ai-btn:disabled { background: #d1d5db; cursor: not-allowed; }
</style>

<script>
window.aiWizardPage = function(prompt) {
    return {
        mode: 'manual',
        step: 1,
        prompt: prompt,
        copied: false,
        rawResult: '',
        parseError: '',
        submitting: false,
        photos: [],        // [{url, file, dataUrl}]
        activePhoto: 0,
        hovHex: '',
        pickedColors: [],  // [{hex, nama}]
        _ctx: null,
        parsed: {
            nama: '', kategori: '', kategori_id: '', jenis: '',
            harga: '', jumlah: '', warna: [],
            catatan: '', video_url: '',
        },
        deskripsi: '',
        warehouseId: '',
        sizeRows: [],   // [{nama, jumlah}] — user isi manual
        _katMap: {},

        init() {
            this.$nextTick(() => {
                const sel = this.$el.querySelector('select.preview-select');
                if (sel) {
                    sel.querySelectorAll('option').forEach(o => {
                        if (o.value) this._katMap[o.textContent.trim().toLowerCase()] = o.value;
                    });
                }
            });
            this.$watch('mode', (v) => this._toggleForm(v));
        },

        setMode(m) {
            this.mode = m;
            console.log('[Atelier] mode →', m);
        },

        _toggleForm(mode) {
            setTimeout(() => {
                const allForms = Array.from(document.querySelectorAll('form'));
                const filamentForm = allForms.find(f =>
                    f.querySelector('[class*="fi-fo"]') ||
                    f.querySelector('[class*="fi-input"]') ||
                    f.querySelector('[class*="fi-select"]')
                ) || allForms.find(f => f.children.length > 0);
                if (!filamentForm) return;
                filamentForm.style.display = mode === 'ai' ? 'none' : '';
            }, 50);
        },

        copyPrompt() {
            navigator.clipboard.writeText(this.prompt).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
            }).catch(() => {
                const el = this.$el.querySelector('.prompt-box');
                if (!el) return;
                const sel = window.getSelection();
                const range = document.createRange();
                range.selectNodeContents(el);
                sel.removeAllRanges();
                sel.addRange(range);
            });
        },

        parse() {
            this.parseError = '';
            const raw = this.rawResult.trim();
            if (!raw) { this.parseError = 'Paste hasil AI terlebih dahulu.'; return; }

            // Parse per-baris: tiap field diawali KEY: value, berhenti di baris berikutnya yang juga KEY:
            const lines = raw.split('\n');
            const fields = {};
            let curKey = null, curVal = [];
            const KEYS = ['NAMA PRODUK','KATEGORI','JENIS PAKAIAN','WARNA DOMINAN','DESKRIPSI','HARGA (RP)','HARGA','UKURAN & STOK','UKURAN DAN STOK','JUMLAH TOTAL','STATUS','CATATAN'];

            lines.forEach(line => {
                const stripped = line.replace(/^[•\-\*━\s]+/, '');
                let matched = null;
                for (const k of KEYS) {
                    if (stripped.toUpperCase().startsWith(k + ':')) {
                        matched = k;
                        break;
                    }
                }
                if (matched) {
                    if (curKey) fields[curKey] = curVal.join(' ').trim();
                    curKey = matched;
                    curVal = [stripped.slice(matched.length + 1).trim()];
                } else if (curKey) {
                    if (stripped) curVal.push(stripped);
                }
            });
            if (curKey) fields[curKey] = curVal.join(' ').trim();

            // Nama: strip jika masih berupa template [...]
            const namaRaw = (fields['NAMA PRODUK'] || '').trim();
            this.parsed.nama = (namaRaw.startsWith('[') && namaRaw.endsWith(']')) ? '' : namaRaw;

            // Kategori: ambil kata pertama saja (strip "Pilihan: ..." dan strip bracket)
            const katRaw = (fields['KATEGORI'] || '')
                .replace(/^Pilihan:.*/i, '').replace(/\[.*\]/g, '').trim()
                .split(/[|,]/)[0].trim();
            this.parsed.kategori = katRaw;
            this.parsed.kategori_id = this._katMap[katRaw.toLowerCase()] || '';

            // Jenis: ambil hanya value sebelum bullet list (• atau →)
            const jenisRaw = (fields['JENIS PAKAIAN'] || '').trim();
            this.parsed.jenis = (jenisRaw.startsWith('[') && jenisRaw.endsWith(']')) ? ''
                : jenisRaw.split(/[•\n]/)[0].trim();
            // Harga — ambil dari AI jika ada angka, kosongkan jika AI tulis "kosongkan" dll
            const hargaRaw = (fields['HARGA'] || fields['HARGA (RP)'] || '').replace(/[^0-9]/g, '');
            this.parsed.harga = hargaRaw || '';
            // Parse JUMLAH TOTAL dari AI (bisa dikosong AI, tapi kita ambil kalau ada)
            const jmlRaw = (fields['JUMLAH TOTAL'] || '').replace(/[^0-9]/g, '');
            this.parsed.jumlah = jmlRaw && jmlRaw !== '0' ? jmlRaw : '';
            this.parsed.video_url = '';

            const warnaRaw = fields['WARNA DOMINAN'] || '';
            this.parsed.warna = [];
            if (warnaRaw && !warnaRaw.toLowerCase().includes('kosong')) {
                warnaRaw.split('|').forEach(p => {
                    const m = p.trim().match(/^(.+?)\s*(#[0-9A-Fa-f]{6})\s*$/);
                    if (m) this.parsed.warna.push({ hex: m[2].toUpperCase(), nama: m[1].trim() });
                });
            }
            // Seed pickedColors dari warna AI
            this.pickedColors = this.parsed.warna.map(w => ({ hex: w.hex, nama: w.nama }));

            // Ukuran & stok tidak diambil dari AI — user isi manual di Step 3
            this.sizeRows = [];

            // Deskripsi dari AI — strip bracket jika masih template
            const deskRaw = (fields['DESKRIPSI'] || '').trim();
            this.deskripsi = (deskRaw.startsWith('[') && deskRaw.endsWith(']')) ? '' : deskRaw;

            this.parsed.catatan = fields['CATATAN'] || '';

            if (!this.parsed.nama) {
                this.parseError = 'NAMA PRODUK tidak ditemukan. Pastikan format output AI sesuai prompt.';
                return;
            }
            this.step = 3;
        },

        // ── Foto & color picker ──
        handlePhotoUpload(e) {
            const files = Array.from(e.target.files || []);
            files.forEach(f => {
                if (this.photos.length >= 5) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.photos.push({ dataUrl: ev.target.result, file: f });
                    if (this.photos.length === 1) this.$nextTick(() => this.loadCanvas(0));
                };
                reader.readAsDataURL(f);
            });
            e.target.value = '';
        },

        loadCanvas(idx) {
            this.activePhoto = idx;
            const cv = this.$refs.photoCanvas;
            if (!cv || !this.photos[idx]) return;
            const img = new Image();
            img.onload = () => {
                const maxW = cv.parentElement.clientWidth || 400, maxH = 260;
                const r = Math.min(maxW / img.width, maxH / img.height, 1);
                cv.width = Math.round(img.width * r);
                cv.height = Math.round(img.height * r);
                const ctx = cv.getContext('2d', { willReadFrequently: true });
                ctx.drawImage(img, 0, 0, cv.width, cv.height);
                this._ctx = ctx;
            };
            img.src = this.photos[idx].dataUrl;
        },

        canvasHover(e) {
            if (!this._ctx) return;
            const cv = this.$refs.photoCanvas;
            const rc = cv.getBoundingClientRect();
            const px = Math.round((e.clientX - rc.left) * cv.width / rc.width);
            const py = Math.round((e.clientY - rc.top) * cv.height / rc.height);
            try {
                const d = this._ctx.getImageData(Math.max(0,px), Math.max(0,py), 1, 1).data;
                if (d[3] > 20) this.hovHex = '#' + [d[0],d[1],d[2]].map(v=>v.toString(16).padStart(2,'0')).join('').toUpperCase();
            } catch(e) {}
        },

        canvasPick(e) {
            if (!this._ctx || this.pickedColors.length >= 8) return;
            const cv = this.$refs.photoCanvas;
            const rc = cv.getBoundingClientRect();
            const px = Math.round((e.clientX - rc.left) * cv.width / rc.width);
            const py = Math.round((e.clientY - rc.top) * cv.height / rc.height);
            try {
                const d = this._ctx.getImageData(Math.max(0,px), Math.max(0,py), 1, 1).data;
                if (d[3] < 20) return;
                const hex = '#' + [d[0],d[1],d[2]].map(v=>v.toString(16).padStart(2,'0')).join('').toUpperCase();
                if (this.pickedColors.some(c => c.hex === hex)) return;
                this.pickedColors.push({ hex, nama: '' });
            } catch(e) {}
        },

        removeColor(i) { this.pickedColors.splice(i, 1); },

        sisaStok() {
            const total = parseInt(this.parsed.jumlah) || 0;
            if (!total) return null; // null = tidak ada batas
            const used = this.sizeRows.reduce((s, r) => s + (parseInt(r.jumlah) || 0), 0);
            return total - used;
        },

        bolehTambahUkuran() {
            const total = parseInt(this.parsed.jumlah) || 0;
            if (!total) return true; // tidak ada batas stok → bebas tambah
            // Jumlah row tidak boleh melebihi total stok (1 row = min 1 item)
            if (this.sizeRows.length >= total) return false;
            // Total qty yang sudah diisi tidak boleh >= total stok
            const sisa = this.sisaStok();
            return sisa === null || sisa > 0;
        },

        addSizeRow() {
            if (!this.bolehTambahUkuran()) return;
            this.sizeRows.push({ nama: '', jumlah: '' });
        },
        removeSizeRow(i) { this.sizeRows.splice(i, 1); },

        submitError: '',

        async submit() {
            this.submitError = '';
            if (!this.parsed.nama) { this.submitError = 'Nama produk wajib diisi.'; return; }
            if (!this.parsed.kategori_id) { this.submitError = 'Kategori wajib dipilih.'; return; }
            this.submitting = true;

            // Ambil wire:id dari data attribute yang di-inject Blade
            const wireId = document.getElementById('atelier-create-wrapper')?.dataset?.wireId;
            const wire = wireId && window.Livewire ? window.Livewire.find(wireId) : null;
            if (!wire) { this.submitError = 'Terjadi kesalahan koneksi. Coba refresh halaman.'; this.submitting = false; return; }

            // Upload foto ke server dulu (jika ada)
            const uploadedPaths = [];
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            for (const p of this.photos) {
                try {
                    const fd = new FormData();
                    fd.append('photo', p.file);
                    fd.append('_token', csrfToken);
                    const res = await fetch('/admin/ai/upload-photo', { method: 'POST', body: fd });
                    if (res.ok) {
                        const json = await res.json();
                        if (json.path) uploadedPaths.push(json.path);
                    }
                } catch(e) { console.warn('Upload foto gagal:', e); }
            }

            // Hitung jumlah total dari sizeRows jika ada
            const validSizes = this.sizeRows.filter(r => r.nama && parseInt(r.jumlah) > 0);
            const totalFromSizes = validSizes.reduce((s, r) => s + parseInt(r.jumlah), 0);
            const jumlah = this.parsed.jumlah
                ? parseInt(this.parsed.jumlah)
                : (totalFromSizes > 0 ? totalFromSizes : 0);

            wire.call('saveFromAi', {
                nama:         this.parsed.nama,
                kategori_id:  this.parsed.kategori_id,
                jenis:        this.parsed.jenis,
                harga:        this.parsed.harga || 0,
                jumlah:       jumlah,
                deskripsi:    this.deskripsi || null,
                warna:        this.pickedColors,
                ukuran:       validSizes,
                warehouse_id: this.warehouseId || null,
                video_url:    this.parsed.video_url,
                photos:       uploadedPaths,
            }).catch(() => {
                this.submitting = false;
                this.submitError = 'Gagal menyimpan. Coba lagi.';
            });
        },
    };
};
</script>

<div id="atelier-create-wrapper"
     data-wire-id="{{ $this->getId() }}"
     x-data="aiWizardPage(@js($prompt))"
     x-init="init()">

    {{-- Toggle --}}
    <div class="ai-mode-toggle">
        <button type="button" class="ai-mode-btn" :class="{ active: mode==='manual' }" @click="setMode('manual')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Mode Manual
        </button>
        <button type="button" class="ai-mode-btn" :class="{ active: mode==='ai' }" @click="setMode('ai')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            Mode AI
        </button>
    </div>

    {{-- Wizard AI — hanya tampil saat mode = ai --}}
    <div x-show="mode === 'ai'" style="display:none">
        <div class="ai-wizard">

            {{-- Step nav --}}
            <div class="ai-steps">
                <div class="ai-step-item" :class="{ active: step===1, done: step>1 }" @click="step>1 && (step=1)">
                    <div class="ai-step-num" x-text="step>1 ? '✓' : '1'"></div>
                    <span>Prompt & Cara Pakai</span>
                </div>
                <div class="ai-step-item" :class="{ active: step===2, done: step>2 }" @click="step>2 && (step=2)">
                    <div class="ai-step-num" x-text="step>2 ? '✓' : '2'"></div>
                    <span>Paste Hasil AI</span>
                </div>
                <div class="ai-step-item" :class="{ active: step===3 }">
                    <div class="ai-step-num">3</div>
                    <span>Preview & Simpan</span>
                </div>
            </div>

            <div class="ai-body">

                {{-- Step 1: Prompt --}}
                <div x-show="step === 1">
                    <div class="prompt-header">
                        <div>
                            <p style="font-size:15px;font-weight:600;color:#111827;margin:0">Prompt untuk AI</p>
                            <p style="font-size:13px;color:#6b7280;margin:4px 0 0">Copy prompt → buka AI → paste + kirim foto produk</p>
                        </div>
                        <button type="button" class="copy-prompt-btn" :class="{ copied: copied }" @click="copyPrompt()">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? 'Tersalin ✓' : 'Copy Prompt'"></span>
                        </button>
                    </div>
                    <div class="prompt-box" x-text="prompt"></div>
                    <div class="how-steps">
                        <div class="how-step"><div class="how-num">1</div><div>Copy prompt di atas dengan tombol <strong>Copy Prompt</strong></div></div>
                        <div class="how-step"><div class="how-num">2</div><div>Buka <strong>asisten AI</strong> pilihan Anda (chatbot yang bisa menerima gambar)</div></div>
                        <div class="how-step"><div class="how-num">3</div><div>Paste prompt + <strong>upload foto produk</strong> dalam satu pesan, lalu kirim</div></div>
                        <div class="how-step"><div class="how-num">4</div><div>Copy seluruh jawaban AI, klik <strong>Lanjut →</strong> dan paste di langkah berikutnya</div></div>
                    </div>
                    <button type="button" class="ai-next-btn" @click="step = 2">Lanjut →</button>
                </div>

                {{-- Step 2: Paste + Upload Foto --}}
                <div x-show="step === 2">
                    <p style="font-size:15px;font-weight:600;color:#111827;margin:0 0 4px">Paste Hasil dari AI</p>
                    <p style="font-size:13px;color:#6b7280;margin:0 0 16px">Copy seluruh jawaban AI dan paste di bawah ini</p>
                    <textarea class="ai-textarea" x-model="rawResult"
                        placeholder="NAMA PRODUK: Kemeja Etnik Motif Floral&#10;KATEGORI: Kasual&#10;JENIS PAKAIAN: Casual Ethnic&#10;..."></textarea>

                    {{-- Upload foto di Step 2 --}}
                    <div style="margin-top:16px;padding:14px;background:#f9fafb;border:1.5px dashed #e5e7eb;border-radius:8px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                            <p style="font-size:13px;font-weight:600;color:#374151;margin:0">
                                Foto Produk
                                <span style="font-weight:400;color:#9ca3af;font-size:12px">(opsional — bisa tambah/ganti di Step 3)</span>
                            </p>
                            <input type="file" x-ref="photoInput" multiple accept="image/*" style="display:none" @change="handlePhotoUpload($event)">
                            <button type="button" @click="$refs.photoInput.click()" x-show="photos.length < 5"
                                style="padding:6px 14px;border:1.5px solid #ea580c;border-radius:7px;background:#fff;color:#ea580c;font-size:12px;cursor:pointer;font-weight:600">
                                + Upload Foto
                            </button>
                        </div>
                        <div x-show="photos.length === 0" style="text-align:center;color:#9ca3af;font-size:12px;padding:8px 0;cursor:pointer" @click="$refs.photoInput.click()">
                            Klik untuk pilih foto produk (maks 5)
                        </div>
                        <div x-show="photos.length > 0" style="display:flex;gap:6px;flex-wrap:wrap">
                            <template x-for="(p, i) in photos" :key="i">
                                <div style="position:relative">
                                    <img :src="p.dataUrl" style="width:56px;height:56px;object-fit:cover;border-radius:7px;border:2px solid #e5e7eb">
                                    <button type="button" @click.stop="photos.splice(i,1)"
                                        style="position:absolute;top:-4px;right:-4px;width:16px;height:16px;background:#ef4444;color:#fff;border:none;border-radius:50%;font-size:10px;cursor:pointer;display:flex;align-items:center;justify-content:center">×</button>
                                </div>
                            </template>
                            <div x-show="photos.length < 5" style="width:56px;height:56px;border:2px dashed #d1d5db;border-radius:7px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#9ca3af;font-size:20px" @click="$refs.photoInput.click()">+</div>
                        </div>
                    </div>

                    <div class="ai-parse-error" x-show="parseError" x-text="parseError"></div>
                    <div>
                        <button type="button" class="ai-back-btn" @click="step = 1">← Kembali</button>
                        <button type="button" class="ai-next-btn" @click="parse()" :disabled="!rawResult.trim()">Proses →</button>
                    </div>
                </div>

                {{-- Step 3: Preview --}}
                <div x-show="step === 3">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap">
                        <p style="font-size:15px;font-weight:600;color:#111827;margin:0">Preview & Finalisasi</p>
                        <span style="font-size:12px;color:#6b7280">Field <span style="color:#16a34a;font-weight:600">hijau</span> = dari AI · kosong = isi sendiri</span>
                    </div>

                    <div class="preview-grid">
                        <div class="preview-field full">
                            <label class="preview-label">Nama Produk <span class="ai-badge" x-show="parsed.nama">AI</span></label>
                            <input type="text" class="preview-input" :class="{ 'ai-filled': parsed.nama }"
                                   x-model="parsed.nama" placeholder="Isi nama produk..." maxlength="60">
                        </div>
                        <div class="preview-field">
                            <label class="preview-label">Kategori <span class="ai-badge" x-show="parsed.kategori_id">AI</span></label>
                            <select class="preview-select" :class="{ 'ai-filled': parsed.kategori_id }" x-model="parsed.kategori_id">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach(\App\Models\GarmentCategory::where('aktif', true)->orderBy('nama')->get() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="preview-field">
                            <label class="preview-label">Jenis Pakaian <span class="ai-badge" x-show="parsed.jenis">AI</span></label>
                            <div class="preview-val" :class="parsed.jenis ? 'ai-filled' : 'empty'"
                                 x-text="parsed.jenis || 'Pilih manual di form setelah simpan'"></div>
                        </div>
                        <div class="preview-field">
                            <label class="preview-label">Harga (Rp) <span style="color:#9ca3af;font-weight:400;font-size:10px">(opsional)</span></label>
                            <input type="number" class="preview-input" x-model="parsed.harga" placeholder="Isi jika sudah diketahui" min="0">
                        </div>
                        <div class="preview-field">
                            <label class="preview-label">
                                Jumlah Stok
                                <span class="ai-badge" x-show="parsed.jumlah">AI</span>
                                <span style="color:#9ca3af;font-weight:400;font-size:10px" x-show="!parsed.jumlah">(opsional)</span>
                            </label>
                            <input type="number" class="preview-input" :class="{ 'ai-filled': parsed.jumlah }"
                                   x-model="parsed.jumlah" placeholder="Isi total stok" min="0">
                            <p x-show="parsed.jumlah && sizeRows.length > 0"
                               style="font-size:11px;margin:3px 0 0"
                               :style="sisaStok() < 0 ? 'color:#dc2626;font-weight:600' : 'color:#6b7280'">
                                Sisa belum dialokasikan: <span x-text="sisaStok()"></span> item
                            </p>
                        </div>
                        <div class="preview-field">
                            <label class="preview-label">Gudang Awal <span style="color:#9ca3af;font-weight:400;font-size:10px">(opsional)</span></label>
                            <select class="preview-select" x-model="warehouseId">
                                <option value="">Pilih gudang (opsional)</option>
                                @foreach(\App\Models\Warehouse::where('status','aktif')->orderBy('nama')->get() as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->nama }}</option>
                                @endforeach
                            </select>
                            <p style="font-size:11px;color:#6b7280;margin:4px 0 0">Gudang default saat unit baru dibuat. Unit bisa dipindah ke gudang lain setelahnya.</p>
                        </div>
                        <div class="preview-field full">
                            <label class="preview-label">Deskripsi Produk <span style="font-weight:400;color:#9ca3af;font-size:11px">(opsional — muncul di halaman publik)</span></label>
                            <textarea x-model="deskripsi" rows="3"
                                placeholder="Tulis deskripsi produk (bahan, detail, ukuran fit, dll)..."
                                style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:13px;color:#111827;resize:vertical;font-family:inherit;box-sizing:border-box;outline:none"
                                @focus="$el.style.borderColor='#ea580c'" @blur="$el.style.borderColor='#e5e7eb'"></textarea>
                        </div>
                        <div class="preview-field full" x-show="parsed.catatan">
                            <label class="preview-label">Catatan AI</label>
                            <div class="catatan-box" x-text="parsed.catatan"></div>
                        </div>
                    </div>

                    {{-- ── Ukuran & Stok (manual) ── --}}
                    <div style="margin-top:20px;padding-top:18px;border-top:1px solid #f3f4f6">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:8px">
                            <div>
                                <p style="font-size:14px;font-weight:600;color:#111827;margin:0">Distribusi Ukuran</p>
                                <p style="font-size:12px;color:#6b7280;margin:2px 0 0">Opsional — alokasikan stok per ukuran</p>
                            </div>
                            <button type="button" @click="addSizeRow()"
                                :disabled="!bolehTambahUkuran()"
                                :style="!bolehTambahUkuran() ? 'opacity:.4;cursor:not-allowed;' : ''"
                                style="padding:7px 16px;border:1.5px solid #ea580c;border-radius:7px;background:#fff;color:#ea580c;font-size:13px;cursor:pointer;font-weight:600">
                                + Tambah Ukuran
                            </button>
                        </div>

                        {{-- Info sisa stok --}}
                        <div x-show="parsed.jumlah" style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:7px;margin-bottom:10px">
                            <span style="font-size:12px;color:#0369a1">Total stok: <strong x-text="parsed.jumlah"></strong> item</span>
                            <span style="color:#94a3b8;font-size:12px">·</span>
                            <span style="font-size:12px" :style="sisaStok() !== null && sisaStok() < 0 ? 'color:#dc2626;font-weight:600' : 'color:#0369a1'">
                                Sisa: <strong x-text="sisaStok() !== null ? sisaStok() : '—'"></strong>
                                <span x-show="sisaStok() !== null && sisaStok() < 0"> ⚠ melebihi stok</span>
                            </span>
                        </div>

                        <div x-show="sizeRows.length === 0" style="padding:14px;text-align:center;background:#f9fafb;border:2px dashed #e5e7eb;border-radius:8px;color:#9ca3af;font-size:13px">
                            Kosongkan untuk tidak pakai distribusi ukuran, atau klik "+ Tambah Ukuran"
                        </div>
                        <div x-show="sizeRows.length > 0" style="display:flex;flex-direction:column;gap:8px">
                            <template x-for="(row, i) in sizeRows" :key="i">
                                <div style="display:flex;gap:10px;align-items:center">
                                    <select x-model="row.nama"
                                        style="flex:1;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:13px;color:#111827;background:#fff;outline:none">
                                        <option value="">Pilih ukuran</option>
                                        <option>S</option><option>M</option><option>L</option>
                                        <option>XL</option><option>XXL</option><option>Free Size</option>
                                    </select>
                                    <input type="number" x-model="row.jumlah" min="1" placeholder="Qty"
                                        :max="parsed.jumlah ? (parseInt(parsed.jumlah) - sisaStok() + (parseInt(row.jumlah)||0)) : undefined"
                                        :style="sisaStok() < 0 ? 'border-color:#fca5a5;' : ''"
                                        style="width:80px;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:13px;color:#111827;outline:none;text-align:center">
                                    <button type="button" @click="removeSizeRow(i)"
                                        style="background:none;border:none;cursor:pointer;color:#d1d5db;font-size:20px;padding:0;line-height:1">&times;</button>
                                </div>
                            </template>
                            <p x-show="!parsed.jumlah" style="font-size:11px;color:#6b7280;margin:4px 0 0">
                                Jumlah total dihitung otomatis dari total ukuran di atas
                            </p>
                        </div>
                    </div>

                    {{-- ── Warna Produk ── --}}
                    <div style="margin-top:20px;padding-top:18px;border-top:1px solid #f3f4f6">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
                            <div>
                                <p style="font-size:14px;font-weight:600;color:#111827;margin:0">
                                    Warna Produk
                                    <span class="ai-badge" x-show="pickedColors.length > 0" style="margin-left:6px">AI</span>
                                </p>
                                <p style="font-size:12px;color:#6b7280;margin:2px 0 0">Dari AI · upload foto untuk ambil warna tambahan</p>
                            </div>
                            <button type="button" @click="pickedColors.push({hex:'#000000',nama:''})" x-show="pickedColors.length < 8"
                                style="padding:7px 14px;border:1.5px solid #e5e7eb;border-radius:7px;background:#fff;color:#6b7280;font-size:13px;cursor:pointer;font-weight:500">
                                + Tambah Manual
                            </button>
                        </div>

                        {{-- Palet warna AI (selalu tampil) --}}
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <template x-for="(c, i) in pickedColors" :key="i">
                                <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px">
                                    <div :style="'width:32px;height:32px;border-radius:7px;background:'+c.hex+';border:2px solid rgba(0,0,0,.1);flex-shrink:0;cursor:pointer'"
                                         @click="$refs['colorInput'+i] && $refs['colorInput'+i].click()"></div>
                                    <input type="color" x-model="c.hex" style="position:absolute;opacity:0;width:1px;height:1px" :x-ref="'colorInput'+i">
                                    <div style="flex:1;min-width:0">
                                        <div style="font-size:12px;font-weight:600;font-family:monospace;color:#374151" x-text="c.hex"></div>
                                        <input type="text" placeholder="Nama warna..." maxlength="40" x-model="c.nama"
                                               style="width:100%;border:none;background:transparent;font-size:12px;color:#6b7280;outline:none;padding:0;margin-top:1px">
                                    </div>
                                    <input type="color" x-model="c.hex"
                                           style="width:28px;height:28px;border:none;background:transparent;cursor:pointer;padding:0;border-radius:4px;flex-shrink:0"
                                           title="Klik untuk ganti warna">
                                    <button type="button" @click="removeColor(i)"
                                        style="background:none;border:none;cursor:pointer;color:#d1d5db;font-size:18px;padding:0;line-height:1;flex-shrink:0">&times;</button>
                                </div>
                            </template>
                            <div x-show="pickedColors.length === 0"
                                 style="padding:14px;text-align:center;background:#f9fafb;border:2px dashed #e5e7eb;border-radius:8px;color:#9ca3af;font-size:13px">
                                Belum ada warna — AI isi otomatis, atau tambah manual
                            </div>
                            <p x-show="pickedColors.length >= 8" style="font-size:11px;color:#f59e0b;margin:2px 0 0">Max 8 warna</p>
                        </div>

                        {{-- Foto produk di Step 3: tampilkan yang sudah diupload + bisa tambah --}}
                        <div style="margin-top:14px">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                                <p style="font-size:13px;font-weight:600;color:#374151;margin:0">
                                    Foto Produk
                                    <span style="font-weight:400;color:#9ca3af;font-size:12px">(<span x-text="photos.length"></span>/5 · klik foto → ambil warna)</span>
                                </p>
                                <input type="file" x-ref="photoInput3" multiple accept="image/*" style="display:none" @change="handlePhotoUpload($event)">
                                <button type="button" @click="$refs.photoInput3.click()" x-show="photos.length < 5"
                                    style="padding:6px 14px;border:1.5px dashed #d1d5db;border-radius:7px;background:#f9fafb;color:#6b7280;font-size:12px;cursor:pointer;font-weight:500">
                                    + Tambah Foto
                                </button>
                            </div>

                            <div x-show="photos.length === 0"
                                 style="padding:16px;text-align:center;background:#f9fafb;border:2px dashed #e5e7eb;border-radius:8px;color:#9ca3af;font-size:12px;cursor:pointer"
                                 @click="$refs.photoInput3.click()">
                                Belum ada foto — klik untuk upload, atau sudah diupload di Step 2
                            </div>

                            <div x-show="photos.length > 0">
                                <div style="border-radius:8px;overflow:hidden;border:2px solid #e5e7eb;cursor:crosshair;background:#f3f4f6;width:100%"
                                     @mousemove="canvasHover($event)"
                                     @mouseleave="hovHex=''"
                                     @click="canvasPick($event)">
                                    <canvas x-ref="photoCanvas" style="display:block;width:100%;height:auto;max-height:300px;object-fit:contain"></canvas>
                                </div>
                                <div style="margin-top:-1px"><!-- wrapper untuk info + thumbnails -->
                                <div style="display:flex;align-items:center;gap:8px;margin-top:5px;min-height:22px">
                                    <div x-show="hovHex" :style="'width:16px;height:16px;border-radius:50%;background:'+hovHex+';border:1.5px solid #d1d5db;flex-shrink:0'"></div>
                                    <span x-show="hovHex" x-text="hovHex" style="font-size:11px;font-weight:600;font-family:monospace;color:#374151"></span>
                                    <span x-show="!hovHex" style="font-size:11px;color:#9ca3af">Arahkan ke foto untuk preview warna</span>
                                </div>
                                <div x-show="photos.length > 1" style="display:flex;gap:5px;flex-wrap:wrap;margin-top:6px">
                                    <template x-for="(p, i) in photos" :key="i">
                                        <div style="position:relative">
                                            <img :src="p.dataUrl" @click.stop="loadCanvas(i)"
                                                 :style="'width:44px;height:44px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid '+(activePhoto===i?'#ea580c':'#e5e7eb')">
                                            <button type="button" @click.stop="photos.splice(i,1); if(activePhoto>=photos.length) loadCanvas(0)"
                                                style="position:absolute;top:-4px;right:-4px;width:15px;height:15px;background:#ef4444;color:#fff;border:none;border-radius:50%;font-size:9px;cursor:pointer;display:flex;align-items:center;justify-content:center">×</button>
                                        </div>
                                    </template>
                                </div>
                                </div>{{-- /wrapper info+thumbnails --}}
                            </div>
                        </div>
                    </div>

                    {{-- Video --}}
                    <div style="margin-top:16px">
                        <label class="preview-label" style="display:block;margin-bottom:6px">Link Video (Opsional)</label>
                        <input type="url" class="preview-input" x-model="parsed.video_url"
                               placeholder="https://www.tiktok.com/...">
                    </div>

                    <div x-show="submitError" style="margin-top:12px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:7px;font-size:13px;color:#b91c1c" x-text="submitError"></div>

                    <div style="display:flex;align-items:center;gap:0">
                        <button type="button" class="ai-back-btn" @click="step = 2">← Kembali</button>
                        <button type="button" class="submit-ai-btn"
                                @click="submit()"
                                :disabled="submitting">
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Produk →'"></span>
                        </button>
                    </div>
                    <p style="font-size:12px;color:#9ca3af;margin:8px 0 0;text-align:center">
                        Setelah simpan → edit produk untuk tambah foto & lengkapi detail
                    </p>
                </div>

            </div>
        </div>
    </div>

</div>

