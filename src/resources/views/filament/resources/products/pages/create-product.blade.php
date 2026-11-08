<x-filament-panels::page>

@php
    $prompt = <<<'PROMPT'
Kamu adalah asisten entri data produk untuk konveksi Atelier (modest fashion & etnik Indonesia).

Tugasmu: analisis foto produk yang saya kirim, lalu isi PERSIS format di bawah ini.
Tidak boleh menambah field lain. Tidak boleh mengubah format.
Jika tidak yakin, pilih yang paling mendekati dari pilihan yang tersedia.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
NAMA PRODUK: [nama singkat & menarik, max 60 karakter. Sebutkan motif/bahan utama jika terlihat jelas]

KATEGORI: [pilih SATU — salin persis]
Pilihan: Kasual | Formal | Khusus | Olahraga | Seragam | Mukena | Anak & Bayi | Aksesoris

JENIS PAKAIAN: [pilih SATU dari daftar ini — salin PERSIS termasuk tanda koma dan slash]

• Kasual       → Kaos polos | Casual Ethnic | Kemeja santai | Tunik panjang | Blus batik longgar | Hoodie | Cardigan | Outer tenun/kimono etnik | Jeans | Celana pendek | Celana palazzo/longgar | Rok batik casual
• Formal       → Kemeja kerja | Blazer | Jas | Gamis kerja/muslim | Dress batik formal | Jas formal | Rok pensil
• Khusus       → Gaun pengantin | Abaya, kaftan | Maxi dress tribal/tenun | Bra | Daster, piyama
• Olahraga     → Jersey | Training pants
• Seragam      → Seragam pramuka | Seragam kantor
• Mukena       → Mukena polos dua setelan | Mukena bordir klasik, bordir mesin | Mukena ringkas bahan ringan | Mukena anak motif lucu | Mukena tenun/batik/songket/jumputan
• Anak & Bayi  → Kaos anak | Blus etnik anak | Dress anak | Dress etnik anak
• Aksesoris    → Topi | Hijab, scarf

WARNA DOMINAN: [3 warna paling dominan — tiap warna: nama Bahasa Indonesia + kode HEX.
Format: Nama #HEXCODE | Nama #HEXCODE | Nama #HEXCODE
Contoh: Biru Tua #1A2E4A | Krem #F5E6D0 | Coklat Muda #8B6347]

HARGA (Rp): [kosongkan — pemilik isi sendiri]

UKURAN & STOK: [daftar ukuran yang cocok untuk jenis produk ini + estimasi distribusi wajar.
Pilihan ukuran: S | M | L | XL | XXL | Free Size
Format: UkuranxJumlah | UkuranxJumlah | ...
Contoh: Sx5 | Mx10 | Lx8 | XLx3
Jika tidak bisa ditentukan dari foto, tulis: Perlu dikonfirmasi]

JUMLAH TOTAL: [kosongkan — pemilik isi sendiri]

STATUS: aktif
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Setelah format di atas, tambahkan:
CATATAN: [hal yang perlu dikonfirmasi pemilik — bahan, ukuran, atau detail yang tidak terlihat jelas di foto]
PROMPT;
@endphp

<style>
.ai-mode-toggle {
    display: flex; gap: 8px; margin-bottom: 24px;
}
.ai-mode-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 20px; border-radius: 8px; font-size: 14px; font-weight: 500;
    border: 1.5px solid #e5e7eb; background: #fff; color: #6b7280;
    cursor: pointer; transition: all .15s;
}
.ai-mode-btn:hover { border-color: #ea580c; color: #ea580c; }
.ai-mode-btn.active { background: #ea580c; border-color: #ea580c; color: #fff; }
.ai-mode-btn svg { width: 16px; height: 16px; }

/* Wizard */
.ai-wizard { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }

/* Step nav */
.ai-steps { display: flex; border-bottom: 1px solid #f3f4f6; background: #fafafa; padding: 0 20px; }
.ai-step-item {
    display: flex; align-items: center; gap: 8px;
    padding: 14px 16px; font-size: 13px; color: #9ca3af;
    border-bottom: 2px solid transparent; cursor: default; user-select: none;
    transition: color .15s;
}
.ai-step-item.done { color: #16a34a; cursor: pointer; }
.ai-step-item.done:hover { color: #15803d; }
.ai-step-item.active { color: #ea580c; border-bottom-color: #ea580c; }
.ai-step-num {
    width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; background: #f3f4f6; color: #9ca3af; flex-shrink: 0;
}
.ai-step-item.active .ai-step-num { background: #ea580c; color: #fff; }
.ai-step-item.done .ai-step-num { background: #16a34a; color: #fff; }

.ai-body { padding: 24px; }

/* Step 1 — prompt */
.prompt-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
.prompt-label { font-size: 11px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: #6b7280; }
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
    max-height: 320px; overflow-y: auto; font-family: inherit;
}
.prompt-box::-webkit-scrollbar { width: 4px; }
.prompt-box::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }

.how-steps { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
.how-step { display: flex; gap: 12px; align-items: flex-start; font-size: 13px; color: #374151; line-height: 1.55; }
.how-num {
    width: 22px; height: 22px; border-radius: 50%; background: #ea580c; color: #fff;
    font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
}

.ai-next-btn {
    margin-top: 24px; padding: 10px 28px; background: #ea580c; color: #fff;
    border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: background .15s;
}
.ai-next-btn:hover { background: #c2410c; }
.ai-back-btn {
    margin-top: 24px; margin-right: 10px; padding: 10px 20px; background: #fff; color: #6b7280;
    border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-weight: 500;
    cursor: pointer; transition: all .15s;
}
.ai-back-btn:hover { border-color: #9ca3af; color: #374151; }

/* Step 2 — paste hasil */
.ai-textarea {
    width: 100%; min-height: 260px; padding: 14px; border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: 13px; line-height: 1.65; color: #111827; resize: vertical; outline: none;
    font-family: inherit; transition: border-color .15s;
}
.ai-textarea:focus { border-color: #ea580c; box-shadow: 0 0 0 3px rgba(234,88,12,.08); }
.ai-parse-error { margin-top: 10px; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 7px; font-size: 13px; color: #b91c1c; }

/* Step 3 — preview */
.preview-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 640px) { .preview-grid { grid-template-columns: 1fr; } }
.preview-field { display: flex; flex-direction: column; gap: 5px; }
.preview-field.full { grid-column: 1 / -1; }
.preview-label { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #6b7280; }
.preview-val {
    padding: 9px 12px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 7px;
    font-size: 13px; color: #111827; min-height: 38px;
}
.preview-val.ai-filled { border-color: #a7f3d0; background: #f0fdf4; color: #065f46; }
.preview-val.empty { color: #9ca3af; font-style: italic; border-style: dashed; }
.preview-input {
    padding: 9px 12px; border: 1.5px solid #e5e7eb; border-radius: 7px;
    font-size: 13px; color: #111827; outline: none; font-family: inherit; width: 100%;
    transition: border-color .15s;
}
.preview-input:focus { border-color: #ea580c; box-shadow: 0 0 0 3px rgba(234,88,12,.08); }
.preview-select {
    padding: 9px 12px; border: 1.5px solid #e5e7eb; border-radius: 7px;
    font-size: 13px; color: #111827; outline: none; font-family: inherit; width: 100%;
    background: #fff; cursor: pointer; transition: border-color .15s;
}
.preview-select:focus { border-color: #ea580c; }

.warna-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.warna-chip {
    display: flex; align-items: center; gap: 6px; padding: 4px 10px 4px 6px;
    border-radius: 100px; border: 1.5px solid #e5e7eb; background: #f9fafb; font-size: 12px; color: #374151;
}
.warna-dot { width: 14px; height: 14px; border-radius: 50%; border: 1px solid rgba(0,0,0,.1); flex-shrink: 0; }

.size-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.size-chip { padding: 4px 12px; border-radius: 6px; background: #f3f4f6; font-size: 12px; font-weight: 600; color: #374151; }

.catatan-box {
    padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 7px;
    font-size: 13px; color: #92400e; line-height: 1.6;
}

.ai-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 100px; padding: 2px 7px; margin-left: 6px; }

.submit-ai-btn {
    width: 100%; padding: 13px; background: #ea580c; color: #fff;
    border: none; border-radius: 9px; font-size: 15px; font-weight: 700;
    cursor: pointer; transition: background .15s; margin-top: 20px;
}
.submit-ai-btn:hover { background: #c2410c; }
.submit-ai-btn:disabled { background: #d1d5db; cursor: not-allowed; }
</style>

{{-- WRAPPER: mode toggle + konten --}}
<div x-data="{ mode: 'manual' }">

    {{-- MODE TOGGLE BUTTONS --}}
    <div class="ai-mode-toggle">
        <button type="button" class="ai-mode-btn" :class="{ active: mode === 'manual' }" @click="mode = 'manual'">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Mode Manual
        </button>
        <button type="button" class="ai-mode-btn" :class="{ active: mode === 'ai' }" @click="mode = 'ai'">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            Mode AI
        </button>
    </div>

    {{-- ── MODE MANUAL ── --}}
    <div x-show="mode === 'manual'">
        <x-filament-panels::form wire:submit="save">
            {{ $this->form }}
        </x-filament-panels::form>
    </div>

    {{-- ── MODE AI — Wizard ── --}}
    <div x-show="mode === 'ai'"
         x-data="aiWizard(@js($prompt))"
         x-init="init()">

        <div class="ai-wizard">

            {{-- Step navigation --}}
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

                {{-- ── STEP 1: Prompt ── --}}
                <div x-show="step === 1">
                    <div class="prompt-header">
                        <div>
                            <p style="font-size:15px;font-weight:600;color:#111827;margin:0">Prompt untuk AI</p>
                            <p style="font-size:13px;color:#6b7280;margin:4px 0 0">Copy prompt ini → buka AI → paste + kirim foto produk</p>
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

                {{-- ── STEP 2: Paste hasil ── --}}
                <div x-show="step === 2">
                    <p style="font-size:15px;font-weight:600;color:#111827;margin:0 0 4px">Paste Hasil dari AI</p>
                    <p style="font-size:13px;color:#6b7280;margin:0 0 16px">Copy seluruh jawaban AI dan paste di bawah ini</p>

                    <textarea
                        class="ai-textarea"
                        x-model="rawResult"
                        placeholder="NAMA PRODUK: Kemeja Etnik Motif Floral&#10;KATEGORI: Kasual&#10;JENIS PAKAIAN: Casual Ethnic&#10;..."></textarea>

                    <div class="ai-parse-error" x-show="parseError" x-text="parseError"></div>

                    <div style="display:flex;gap:0;align-items:center;margin-top:0">
                        <button type="button" class="ai-back-btn" @click="step = 1">← Kembali</button>
                        <button type="button" class="ai-next-btn" @click="parse()" :disabled="!rawResult.trim()">
                            Proses Hasil →
                        </button>
                    </div>
                </div>

                {{-- ── STEP 3: Preview & Finalisasi ── --}}
                <div x-show="step === 3">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap">
                        <p style="font-size:15px;font-weight:600;color:#111827;margin:0">Preview & Finalisasi</p>
                        <span style="font-size:12px;color:#6b7280">Field <span style="color:#16a34a;font-weight:600">hijau</span> = diisi AI · Field kosong = isi sendiri</span>
                    </div>

                    <div class="preview-grid">

                        {{-- Nama Produk --}}
                        <div class="preview-field full">
                            <label class="preview-label">Nama Produk <span class="ai-badge" x-show="parsed.nama">AI</span></label>
                            <input type="text" class="preview-input" :class="{ 'ai-filled': parsed.nama }"
                                   x-model="parsed.nama" placeholder="Isi nama produk..." maxlength="60" required>
                        </div>

                        {{-- Kategori --}}
                        <div class="preview-field">
                            <label class="preview-label">Kategori <span class="ai-badge" x-show="parsed.kategori">AI</span></label>
                            <select class="preview-select" :class="{ 'ai-filled': parsed.kategori_id }" x-model="parsed.kategori_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach(\App\Models\GarmentCategory::where('aktif', true)->orderBy('nama')->get() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jenis Pakaian --}}
                        <div class="preview-field">
                            <label class="preview-label">Jenis Pakaian <span class="ai-badge" x-show="parsed.jenis">AI</span></label>
                            <div class="preview-val" :class="parsed.jenis ? 'ai-filled' : 'empty'"
                                 x-text="parsed.jenis || 'Akan dipilih dari form manual'"></div>
                            <p style="font-size:11px;color:#9ca3af;margin:3px 0 0">Jenis pakaian dikonfirmasi di form setelah simpan</p>
                        </div>

                        {{-- Harga --}}
                        <div class="preview-field">
                            <label class="preview-label">Harga (Rp)</label>
                            <input type="number" class="preview-input" x-model="parsed.harga" placeholder="0" min="0" required>
                        </div>

                        {{-- Jumlah --}}
                        <div class="preview-field">
                            <label class="preview-label">Jumlah Total (item)</label>
                            <input type="number" class="preview-input" x-model="parsed.jumlah" placeholder="0" min="0" required>
                        </div>

                        {{-- Status --}}
                        <div class="preview-field">
                            <label class="preview-label">Status <span class="ai-badge">AI</span></label>
                            <div class="preview-val ai-filled">aktif</div>
                        </div>

                        {{-- Warna --}}
                        <div class="preview-field full">
                            <label class="preview-label">Warna Dominan <span class="ai-badge" x-show="parsed.warna.length">AI</span></label>
                            <div x-show="parsed.warna.length">
                                <div class="warna-chips">
                                    <template x-for="w in parsed.warna" :key="w.hex">
                                        <div class="warna-chip">
                                            <div class="warna-dot" :style="'background:' + w.hex"></div>
                                            <span x-text="w.nama + ' ' + w.hex"></span>
                                        </div>
                                    </template>
                                </div>
                                <p style="font-size:11px;color:#9ca3af;margin:6px 0 0">Warna ini akan dimasukkan ke color picker setelah form terbuka</p>
                            </div>
                            <div class="preview-val empty" x-show="!parsed.warna.length">Tidak terdeteksi — gunakan color picker manual</div>
                        </div>

                        {{-- Ukuran & Stok --}}
                        <div class="preview-field full">
                            <label class="preview-label">Ukuran & Stok <span class="ai-badge" x-show="parsed.ukuran.length">AI</span></label>
                            <div x-show="parsed.ukuran.length">
                                <div class="size-chips">
                                    <template x-for="u in parsed.ukuran" :key="u.nama">
                                        <div class="size-chip" x-text="u.nama + ' ×' + u.jumlah"></div>
                                    </template>
                                </div>
                                <p style="font-size:11px;color:#9ca3af;margin:6px 0 0">Distribusi ukuran akan diisi ke form Stok per Ukuran</p>
                            </div>
                            <div class="preview-val empty" x-show="!parsed.ukuran.length">Perlu dikonfirmasi — isi manual di form</div>
                        </div>

                        {{-- Catatan --}}
                        <div class="preview-field full" x-show="parsed.catatan">
                            <label class="preview-label">Catatan dari AI</label>
                            <div class="catatan-box" x-text="parsed.catatan"></div>
                        </div>

                    </div>

                    {{-- Upload foto --}}
                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #f3f4f6">
                        <p style="font-size:14px;font-weight:600;color:#111827;margin:0 0 4px">Upload Foto Produk</p>
                        <p style="font-size:13px;color:#6b7280;margin:0 0 14px">Minimal 1 foto, maksimal 5 foto</p>
                        <input type="file" id="ai-photos" multiple accept="image/*" style="display:none"
                               @change="handlePhotos($event)">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
                            <template x-for="(p, i) in photoPreview" :key="i">
                                <div style="position:relative">
                                    <img :src="p" style="width:70px;height:70px;object-fit:cover;border-radius:8px;border:2px solid #e5e7eb">
                                    <button type="button" @click="removePhoto(i)"
                                        style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;background:#ef4444;color:#fff;border:none;border-radius:50%;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1">×</button>
                                </div>
                            </template>
                            <button type="button" @click="$refs.photoInput.click()" x-show="photoPreview.length < 5"
                                style="width:70px;height:70px;border:2px dashed #d1d5db;border-radius:8px;background:#f9fafb;color:#9ca3af;font-size:24px;cursor:pointer;display:flex;align-items:center;justify-content:center"
                                x-ref="photoBtnRef">+</button>
                        </div>
                        <input type="file" x-ref="photoInput" multiple accept="image/*" style="display:none" @change="handlePhotos($event)">
                    </div>

                    {{-- Video --}}
                    <div style="margin-top:16px">
                        <label class="preview-label" style="display:block;margin-bottom:6px">Link Video (Opsional)</label>
                        <input type="url" class="preview-input" x-model="parsed.video_url"
                               placeholder="https://www.tiktok.com/... atau https://www.instagram.com/...">
                    </div>

                    <div style="display:flex;gap:0;align-items:center">
                        <button type="button" class="ai-back-btn" @click="step = 2">← Kembali</button>
                        <button type="button" class="submit-ai-btn" style="width:auto;flex:1"
                                @click="submitToForm()"
                                :disabled="!parsed.nama || !parsed.kategori_id || !parsed.harga || !parsed.jumlah">
                            Simpan Produk →
                        </button>
                    </div>
                    <p style="font-size:12px;color:#9ca3af;margin:8px 0 0;text-align:center">
                        Setelah simpan, Anda bisa edit jenis pakaian & warna di form edit produk
                    </p>

                </div>

            </div>
        </div>
    </div>

</div>{{-- /wrapper x-data mode --}}

<script>
function aiWizard(prompt) {
    return {
        step: 1,
        prompt: prompt,
        copied: false,
        rawResult: '',
        parseError: '',
        photoFiles: [],
        photoPreview: [],
        parsed: {
            nama: '',
            kategori: '',
            kategori_id: '',
            jenis: '',
            harga: '',
            jumlah: '',
            status: 'aktif',
            warna: [],
            ukuran: [],
            catatan: '',
            video_url: '',
        },

        // map nama kategori → id (diisi dari options)
        _katMap: {},

        init() {
            // Build kategori map dari select options
            this.$nextTick(() => {
                const sel = this.$el.querySelector('select.preview-select');
                if (sel) {
                    sel.querySelectorAll('option').forEach(o => {
                        if (o.value) this._katMap[o.textContent.trim().toLowerCase()] = o.value;
                    });
                }
            });
        },

        copyPrompt() {
            navigator.clipboard.writeText(this.prompt).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
            }).catch(() => {
                // fallback select
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

            const get = (key) => {
                const re = new RegExp(key + '[\\s\\S]*?:\\s*(.+?)(?=\\n[A-Z\\u2014━]|$)', 'i');
                const m = raw.match(re);
                return m ? m[1].trim() : '';
            };

            // Nama
            this.parsed.nama = get('NAMA PRODUK');

            // Kategori — cari di map
            const katRaw = get('KATEGORI').replace(/^Pilihan:.*/i, '').trim();
            this.parsed.kategori = katRaw;
            this.parsed.kategori_id = this._katMap[katRaw.toLowerCase()] || '';

            // Jenis
            this.parsed.jenis = get('JENIS PAKAIAN');

            // Harga — strip non-digit
            const hargaRaw = get('HARGA');
            const hargaNum = hargaRaw.replace(/[^\d]/g, '');
            this.parsed.harga = hargaNum && hargaNum !== '0' ? hargaNum : '';

            // Jumlah — kosong dulu
            this.parsed.jumlah = '';

            // Status
            this.parsed.status = 'aktif';

            // Warna — parse "Nama #HEX | Nama #HEX | Nama #HEX"
            const warnaRaw = get('WARNA DOMINAN');
            this.parsed.warna = [];
            if (warnaRaw && !warnaRaw.toLowerCase().includes('kosong') && !warnaRaw.toLowerCase().includes('perlu')) {
                const parts = warnaRaw.split('|');
                parts.forEach(p => {
                    const m = p.trim().match(/^(.+?)\s*(#[0-9A-Fa-f]{6})\s*$/);
                    if (m) {
                        this.parsed.warna.push({ nama: m[1].trim(), hex: m[2].toUpperCase() });
                    }
                });
            }

            // Ukuran — parse "Sx5 | Mx10 | ..."
            const ukuranRaw = get('UKURAN & STOK') || get('UKURAN DAN STOK');
            this.parsed.ukuran = [];
            if (ukuranRaw && !ukuranRaw.toLowerCase().includes('perlu') && !ukuranRaw.toLowerCase().includes('dikonfirmasi')) {
                const parts = ukuranRaw.split('|');
                parts.forEach(p => {
                    const m = p.trim().match(/^([A-Za-z\s]+)[xX×*](\d+)$/);
                    if (m) {
                        this.parsed.ukuran.push({ nama: m[1].trim().toUpperCase(), jumlah: parseInt(m[2]) });
                    }
                });
            }

            // Catatan
            this.parsed.catatan = get('CATATAN');

            // Validasi minimal
            if (!this.parsed.nama) {
                this.parseError = 'NAMA PRODUK tidak ditemukan. Pastikan format output AI sesuai prompt.';
                return;
            }

            this.step = 3;
        },

        handlePhotos(e) {
            const files = Array.from(e.target.files || []);
            files.forEach(f => {
                if (this.photoFiles.length >= 5) return;
                this.photoFiles.push(f);
                const reader = new FileReader();
                reader.onload = (ev) => this.photoPreview.push(ev.target.result);
                reader.readAsDataURL(f);
            });
            e.target.value = '';
        },

        removePhoto(i) {
            this.photoFiles.splice(i, 1);
            this.photoPreview.splice(i, 1);
        },

        submitToForm() {
            if (!this.parsed.nama || !this.parsed.kategori_id) return;
            if (!this.parsed.harga || !this.parsed.jumlah) {
                alert('Isi Harga dan Jumlah terlebih dahulu.');
                return;
            }

            // Kirim ke Livewire untuk simpan
            @this.call('saveFromAi', {
                nama:         this.parsed.nama,
                kategori_id:  this.parsed.kategori_id,
                jenis:        this.parsed.jenis,
                harga:        this.parsed.harga,
                jumlah:       this.parsed.jumlah,
                status:       'aktif',
                warna:        this.parsed.warna,
                ukuran:       this.parsed.ukuran,
                video_url:    this.parsed.video_url,
            });
        },
    };
}
</script>

</x-filament-panels::page>
