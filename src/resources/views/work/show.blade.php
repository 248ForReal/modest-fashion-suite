@extends('layouts.app')
@section('title', 'Detail Tugas - Atelier')
@section('back', route('work.index'))
@section('page_title', 'Detail Tugas')

@section('content')
<div class="space-y-4">

    {{-- Info Tugas --}}
    @php
        $isOwner      = $assignment->assigned_to === auth()->id();
        $isPool       = $assignment->isPool();
        $isSelfAssign = $isOwner && $assignment->created_by === auth()->id();
        $unclaimed = $isPool && $assignment->status === 'open' && is_null($assignment->assigned_to);
        $statusLabels = [
            'open'             => ['label' => 'Belum Dimulai',      'bg' => 'bg-gray-100',   'text' => 'text-gray-600'],
            'in_progress'      => ['label' => 'Sedang Dikerjakan',  'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
            'submitted'        => ['label' => 'Menunggu Review',    'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
            'revision'         => ['label' => 'Perlu Revisi',       'bg' => 'bg-red-100',    'text' => 'text-red-700'],
            'approved'         => ['label' => 'Disetujui',          'bg' => 'bg-green-100',  'text' => 'text-green-700'],
            'rejected'         => ['label' => 'Ditolak',            'bg' => 'bg-red-100',    'text' => 'text-red-700'],
            'cancel_requested' => ['label' => 'Menunggu Cancel',    'bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
            'cancelled'        => ['label' => 'Dibatalkan',         'bg' => 'bg-gray-100',   'text' => 'text-gray-500'],
        ];
        $s = $statusLabels[$assignment->status] ?? $statusLabels['open'];
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $s['bg'] }} {{ $s['text'] }}">
                    {{ $s['label'] }}
                </span>
                @if($isPool)
                <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-yellow-100 text-yellow-700">
                    Pool — Rebutan
                </span>
                @endif
            </div>
            @if($assignment->deadline)
            <span class="text-xs text-gray-400">
                Deadline: <b class="text-gray-600">{{ $assignment->deadline->format('d M Y') }}</b>
            </span>
            @endif
        </div>

        <h2 class="text-base font-bold text-gray-800">{{ $assignment->garmentType->contoh_pakaian }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $assignment->garmentType->kategori }} — {{ $assignment->garmentType->sub_kategori }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400">Jenis Pekerjaan</p>
                <p class="text-sm font-semibold text-gray-700 mt-0.5">{{ $assignment->workType->nama }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400">Target</p>
                <p class="text-sm font-semibold text-gray-700 mt-0.5">{{ $assignment->target_jumlah }} item</p>
            </div>
        </div>

        @if($assignment->instruksi || $assignment->foto_instruksi || $assignment->link_instruksi)
        <div class="mt-3 bg-orange-50 border border-orange-100 rounded-xl p-3 space-y-3">
            <p class="text-xs text-orange-500 font-medium">{{ $isSelfAssign ? 'Catatan Pengerjaan' : 'Instruksi dari Supervisor' }}</p>

            @if($assignment->instruksi)
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $assignment->instruksi }}</p>
            @endif

            @if($assignment->foto_instruksi)
            <div>
                <p class="text-xs text-gray-400 mb-1">Foto Referensi</p>
                <a href="{{ Storage::url($assignment->foto_instruksi) }}" target="_blank">
                    <img src="{{ Storage::url($assignment->foto_instruksi) }}"
                        alt="Foto instruksi"
                        class="rounded-xl max-h-64 w-full object-cover border border-orange-100 hover:opacity-90 transition-opacity cursor-zoom-in">
                </a>
            </div>
            @endif

            @if($assignment->link_instruksi)
            <a href="{{ $assignment->link_instruksi }}" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-2 text-sm text-orange-600 hover:text-orange-700 font-medium">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <span class="truncate">{{ $assignment->link_instruksi }}</span>
                <svg class="w-3 h-3 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
            @endif
        </div>
        @endif

        <p class="text-xs text-gray-400 mt-3">Dari: {{ $assignment->createdBy->name }}</p>
    </div>

    {{-- Catatan Revisi (jika ada) --}}
    @if($assignment->status === 'revision')
    @php $latestReview = $assignment->submissions->last()?->review; @endphp
    @if($latestReview)
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
        <p class="text-sm font-semibold text-red-600 mb-1">Catatan Revisi</p>
        <p class="text-sm text-red-700">{{ $latestReview->catatan_review ?? 'Silakan perbaiki dan kirim ulang.' }}</p>
    </div>
    @endif
    @endif

    {{-- Edit & Hapus untuk tugas mandiri (created_by === assigned_to) --}}
    @if($isSelfAssign && $assignment->status === 'in_progress')
    <div x-data="{ editOpen: false, deleteOpen: false }" class="space-y-2">

        {{-- Tombol aksi --}}
        <div class="flex gap-2">
            <button @click="editOpen = !editOpen; deleteOpen = false" type="button"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-orange-200 text-orange-500 rounded-2xl text-sm font-medium hover:bg-orange-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Tugas
            </button>
            <button @click="deleteOpen = !deleteOpen; editOpen = false" type="button"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-red-200 text-red-400 rounded-2xl text-sm font-medium hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Tugas
            </button>
        </div>

        {{-- Form Edit --}}
        <div x-show="editOpen" x-transition class="bg-white rounded-2xl border border-orange-100 shadow-sm p-5"
            x-data="selfEditForm()">
            <p class="text-sm font-semibold text-gray-700 mb-4">Edit Tugas Mandiri</p>
            <form action="{{ route('work.self-edit', $assignment) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Kategori Pakaian</label>
                    <select name="_kategori" x-model="kategori" @change="jenis = ''"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white" required>
                        <option value="">Pilih kategori...</option>
                        @foreach($garmentKategori as $katId => $katNama)
                        <option value="{{ $katId }}" {{ $assignment->garmentType?->garment_category_id == $katId ? 'selected' : '' }}>{{ $katNama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Jenis Pakaian</label>
                    <select name="garment_type_id" x-model="jenis"
                        :disabled="!kategori"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white disabled:opacity-50" required>
                        <option value="">Pilih jenis pakaian...</option>
                        @foreach($garmentByKategori as $katId => $items)
                        <template x-if="kategori == '{{ $katId }}'">
                            <template x-for="item in {{ $items->map(fn($i) => ['id' => $i->id, 'nama' => $i->contoh_pakaian])->values()->toJson() }}" :key="item.id">
                                <option :value="item.id" x-text="item.nama" :selected="item.id == {{ $assignment->garment_type_id }}"></option>
                            </template>
                        </template>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Jenis Pekerjaan</label>
                    <select name="work_type_id"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white" required>
                        @foreach($workTypes as $wt)
                        <option value="{{ $wt->id }}" {{ $assignment->work_type_id === $wt->id ? 'selected' : '' }}>{{ $wt->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Target Jumlah (item)</label>
                    <input type="number" name="target_jumlah" min="1" max="999"
                        value="{{ $assignment->target_jumlah }}"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white" required>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Instruksi / Catatan (opsional)</label>
                    <textarea name="instruksi" rows="3" maxlength="500"
                        placeholder="Keterangan pengerjaan, detail ukuran, dll."
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white resize-none">{{ $assignment->instruksi }}</textarea>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="editOpen = false"
                        class="flex-1 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm font-medium hover:bg-gray-50 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-orange-400 text-white rounded-xl text-sm font-semibold hover:bg-orange-500 transition-all">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        {{-- Konfirmasi Hapus --}}
        <div x-show="deleteOpen" x-transition class="bg-red-50 border border-red-100 rounded-2xl p-4">
            <p class="text-sm font-semibold text-red-600 mb-1">Hapus Tugas Ini?</p>
            <p class="text-xs text-red-500 mb-3">Tugas akan diarsipkan dan tetap tercatat di sistem. Kamu bisa membuat tugas baru setelahnya.</p>
            <form action="{{ route('work.self-delete', $assignment) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-medium text-red-600 mb-1 block">Alasan menghapus <span class="text-red-400">*</span></label>
                    <textarea name="alasan" rows="3" required minlength="5" maxlength="500"
                        placeholder="Jelaskan kenapa tugas ini dihapus..."
                        class="w-full text-sm border border-red-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-red-400 resize-none"></textarea>
                    @error('alasan')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="deleteOpen = false"
                        class="flex-1 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm font-medium bg-white hover:bg-gray-50 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-red-500 text-white rounded-xl text-sm font-semibold hover:bg-red-600 transition-all">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>

    </div>
    @endif

    {{-- Tombol Claim (tugas pool belum diklaim) --}}
    @if($unclaimed)
    <form action="{{ route('work.claim', $assignment) }}" method="POST">
        @csrf
        <button type="submit"
            class="w-full py-3 bg-yellow-500 text-white rounded-2xl font-semibold text-sm hover:bg-yellow-600 active:scale-95 transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
            </svg>
            Ambil Tugas Ini
        </button>
    </form>
    @endif

    {{-- Tombol Mulai (tugas personal belum dimulai) --}}
    @if($isOwner && $assignment->status === 'open')
    <form action="{{ route('work.start', $assignment) }}" method="POST">
        @csrf
        <button type="submit"
            class="w-full py-3 bg-orange-500 text-white rounded-2xl font-semibold text-sm hover:bg-orange-600 active:scale-95 transition-all">
            Mulai Kerjakan
        </button>
    </form>
    @endif

    {{-- Form Submit Hasil --}}
    @if($isOwner && in_array($assignment->status, ['in_progress', 'revision']))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Input Hasil Pekerjaan</p>
        <form id="submitForm" action="{{ route('work.submit', $assignment) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Jumlah selesai --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Jumlah Item Selesai <span class="text-red-400">*</span></label>
                <input type="number" name="jumlah_selesai" min="1" max="{{ $assignment->target_jumlah }}"
                    value="{{ old('jumlah_selesai') }}"
                    placeholder="Maks {{ $assignment->target_jumlah }} item"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 @error('jumlah_selesai') border-red-400 @enderror"
                    required>
                @error('jumlah_selesai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
                <textarea name="catatan" rows="3"
                    placeholder="Kendala, keterangan tambahan, dll."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none">{{ old('catatan') }}</textarea>
            </div>

            {{-- Foto bukti --}}
            <div>
                <label class="block text-xs text-gray-500 mb-2">Foto Bukti <span class="text-red-400">*</span></label>

                {{-- Hidden input yang akan diisi hasil kompresi --}}
                <input type="file" name="foto" id="fotoInput" accept="image/*" class="hidden">
                <input type="file" id="kameraInput" accept="image/*" capture="environment" class="hidden">
                <input type="hidden" name="foto_base64" id="fotoBase64">

                {{-- Tombol kamera & upload --}}
                <div id="fotoButtons" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <button type="button" onclick="document.getElementById('kameraInput').click()"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border:2px dashed #fed7aa;border-radius:14px;background:#fff7ed;color:#ea580c;font-size:13px;font-weight:600;cursor:pointer;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
                        </svg>
                        Buka Kamera
                    </button>
                    <button type="button" onclick="document.getElementById('fotoInput').click()"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border:2px dashed #e5e7eb;border-radius:14px;background:#f9fafb;color:#6b7280;font-size:13px;font-weight:600;cursor:pointer;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Pilih Galeri
                    </button>
                </div>

                {{-- Loading kompresi --}}
                <div id="fotoLoading" style="display:none;text-align:center;padding:16px;">
                    <div style="display:inline-flex;align-items:center;gap:10px;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:10px 16px;">
                        <svg style="animation:spin 1s linear infinite;width:18px;height:18px;color:#ea580c;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                        <span style="font-size:13px;color:#ea580c;font-weight:500;">Mengompresi foto...</span>
                    </div>
                </div>

                {{-- Preview foto --}}
                <div id="fotoPreview" style="display:none;margin-top:10px;position:relative;">
                    <img id="fotoPreviewImg" src="" alt="Preview"
                        style="width:100%;max-height:220px;object-fit:contain;border-radius:12px;border:1px solid #e5e7eb;background:#f9fafb;">
                    <div id="fotoInfo" style="font-size:11px;color:#9ca3af;text-align:center;margin-top:4px;"></div>
                    <button type="button" onclick="resetFoto()"
                        style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,0.5);color:white;border:none;border-radius:50%;width:26px;height:26px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;">✕</button>
                </div>

                @error('foto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit button --}}
            <button type="submit" id="submitBtn"
                class="w-full py-3 bg-orange-500 text-white rounded-2xl font-semibold text-sm hover:bg-orange-600 active:scale-95 transition-all">
                Kirim Hasil
            </button>

            {{-- Loading submit --}}
            <div id="submitLoading" style="display:none;text-align:center;padding:8px;">
                <div style="display:inline-flex;align-items:center;gap:10px;color:#ea580c;">
                    <svg style="animation:spin 1s linear infinite;width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    <span style="font-size:13px;font-weight:500;">Mengirim hasil...</span>
                </div>
            </div>
        </form>
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <script>
    var fotoReady = false;

    function processImage(file) {
        document.getElementById('fotoLoading').style.display = 'block';
        document.getElementById('fotoButtons').style.display = 'none';
        document.getElementById('fotoPreview').style.display = 'none';
        fotoReady = false;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let w = img.width, h = img.height;
                const maxW = 1280;
                if (w > maxW) { h = Math.round(h * maxW / w); w = maxW; }
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.75);

                canvas.toBlob(function(blob) {
                    // Coba pakai DataTransfer (modern browsers)
                    try {
                        const compressed = new File([blob], 'foto-bukti.jpg', { type: 'image/jpeg' });
                        const dt = new DataTransfer();
                        dt.items.add(compressed);
                        document.getElementById('fotoInput').files = dt.files;
                        document.getElementById('fotoBase64').value = '';
                    } catch(err) {
                        // Fallback: kirim base64 (browser lama / iOS lama)
                        document.getElementById('fotoBase64').value = dataUrl;
                        document.getElementById('fotoInput').value = '';
                    }

                    fotoReady = true;

                    // Preview
                    document.getElementById('fotoPreviewImg').src = dataUrl;
                    const kb = Math.round(blob.size / 1024);
                    document.getElementById('fotoInfo').textContent = w + ' × ' + h + 'px · ' + kb + ' KB (sudah dikompresi)';
                    document.getElementById('fotoLoading').style.display = 'none';
                    document.getElementById('fotoPreview').style.display = 'block';
                }, 'image/jpeg', 0.75);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function resetFoto() {
        document.getElementById('fotoInput').value = '';
        document.getElementById('kameraInput').value = '';
        document.getElementById('fotoBase64').value = '';
        document.getElementById('fotoPreview').style.display = 'none';
        document.getElementById('fotoButtons').style.display = 'grid';
        fotoReady = false;
    }

    document.getElementById('fotoInput').addEventListener('change', function() {
        if (this.files[0]) processImage(this.files[0]);
    });

    document.getElementById('kameraInput').addEventListener('change', function() {
        if (this.files[0]) processImage(this.files[0]);
    });

    document.getElementById('submitForm').addEventListener('submit', function(e) {
        const hasFile = document.getElementById('fotoInput').files.length > 0;
        const hasBase64 = document.getElementById('fotoBase64').value.length > 0;
        if (!hasFile && !hasBase64) {
            e.preventDefault();
            alert('Foto bukti wajib dilampirkan.');
            return;
        }
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').style.display = 'none';
        document.getElementById('submitLoading').style.display = 'block';
    });
    </script>
    @endif

    {{-- Cancel Request --}}
    @if($isOwner && in_array($assignment->status, ['open', 'in_progress', 'submitted', 'revision']))
    @php
        $hasPendingCancel = $assignment->latestCancelRequest?->isPending();
    @endphp
    @if($hasPendingCancel)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-700">Permintaan Cancel Sedang Diproses</p>
            <p class="text-xs text-amber-600 mt-0.5">Alasan: {{ $assignment->latestCancelRequest->alasan }}</p>
            <p class="text-xs text-amber-500 mt-0.5">Menunggu persetujuan supervisor.</p>
        </div>
    </div>
    @else
    <div x-data="{ open: false }">
        <button @click="open = !open" type="button"
            class="w-full py-2.5 border border-red-200 text-red-500 rounded-2xl font-medium text-sm hover:bg-red-50 active:scale-95 transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Ajukan Pembatalan Tugas
        </button>
        <div x-show="open" x-transition class="mt-3 bg-white border border-red-100 rounded-2xl p-4">
            <p class="text-sm font-semibold text-gray-700 mb-3">Alasan Pembatalan</p>
            <form action="{{ route('work.cancel-request', $assignment) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <textarea name="alasan" rows="3" required minlength="10" maxlength="500"
                        placeholder="Jelaskan alasan kamu ingin membatalkan tugas ini (min. 10 karakter)..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-200 resize-none @error('alasan') border-red-400 @enderror">{{ old('alasan') }}</textarea>
                    @error('alasan')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="open = false"
                        class="flex-1 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm font-medium hover:bg-gray-50 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-red-500 text-white rounded-xl text-sm font-semibold hover:bg-red-600 active:scale-95 transition-all">
                        Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endif

    {{-- Status cancel_requested info for owner --}}
    @if($isOwner && $assignment->status === 'cancel_requested')
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-700">Permintaan Cancel Sedang Diproses</p>
            @if($assignment->latestCancelRequest)
            <p class="text-xs text-amber-600 mt-0.5">Alasan: {{ $assignment->latestCancelRequest->alasan }}</p>
            @endif
            <p class="text-xs text-amber-500 mt-0.5">Menunggu persetujuan supervisor.</p>
        </div>
    </div>
    @endif

    {{-- Riwayat Submission --}}
    @if($isOwner && $assignment->submissions->isNotEmpty())
    <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Riwayat Submit</p>
        @foreach($assignment->submissions->sortByDesc('created_at') as $submission)
        <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-3">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1">
                    <p class="text-sm text-gray-700"><b>{{ $submission->jumlah_selesai }}</b> item selesai</p>
                    @if($submission->catatan)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $submission->catatan }}</p>
                    @endif
                    @if($submission->foto_path)
                    <a href="{{ Storage::url($submission->foto_path) }}" target="_blank"
                        class="text-xs text-orange-500 mt-1 inline-block">Lihat Foto →</a>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-semibold text-orange-500">{{ number_format($submission->nilai_poin, 2) }} poin</p>
                    <p class="text-xs text-gray-400">{{ $submission->created_at->format('d M, H:i') }}</p>
                </div>
            </div>
            @if($submission->review)
            <div class="mt-2 pt-2 border-t border-gray-50 flex items-center gap-2">
                @if($submission->review->status === 'approved')
                <span class="text-xs text-green-600 font-medium">✓ Disetujui</span>
                @elseif($submission->review->status === 'revision')
                <span class="text-xs text-red-500 font-medium">↩ Revisi</span>
                @else
                <span class="text-xs text-red-500 font-medium">✗ Ditolak</span>
                @endif
                @if($submission->review->skor_kualitas)
                <span class="text-xs text-yellow-500">★ {{ $submission->review->skor_kualitas }}/5</span>
                @endif
                @if($submission->review->catatan_review)
                <span class="text-xs text-gray-400">— {{ $submission->review->catatan_review }}</span>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

</div>

@push('head')
<script src="/js/sweetalert2.min.js"></script>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('selfEditForm', () => ({
        kategori: '{{ $assignment->garmentType?->garment_category_id ?? '' }}',
        jenis: '{{ $assignment->garment_type_id ?? '' }}',
    }));
});
</script>
@endpush
@endsection
