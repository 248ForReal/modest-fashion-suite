@extends('layouts.app')
@section('title', 'Dashboard - Atelier')

@section('content')
<div class="space-y-5">

    {{-- Greeting + Jam --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-xl font-bold text-orange-500 shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs">Selamat datang kembali,</p>
                    <h2 class="text-base font-bold text-gray-800 truncate">{{ auth()->user()->name }}</h2>
                    <p class="text-xs text-orange-400 mt-0.5">{{ auth()->user()->getRoleNames()->join(', ') ?: '-' }}</p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-2xl font-bold text-gray-800" id="dashboard-clock">{{ now()->format('H:i') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        {{-- Status absen hari ini --}}
        <div class="mt-4 pt-4 border-t border-gray-50 flex gap-4">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full {{ $clockedIn ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                <span class="text-xs {{ $clockedIn ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                    {{ $clockedIn ? 'Sudah absen masuk' : 'Belum absen masuk' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full {{ $clockedOut ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                <span class="text-xs {{ $clockedOut ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                    {{ $clockedOut ? 'Sudah absen pulang' : 'Belum absen pulang' }}
                </span>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Jam kerja: <span class="font-medium text-gray-600">{{ $workStart }} – {{ $workEnd }}</span></p>
    </div>

    @if(auth()->user()->status === 'pending')
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-sm text-yellow-700">
        Akun Anda sedang menunggu persetujuan admin.
    </div>
    @endif

    {{-- Tugas (magang/siswa/education) --}}
    @can('work.view_own')
    @php
        $statusColors = [
            'open'             => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'label' => 'Belum Dimulai',     'border' => 'border-orange-200'],
            'in_progress'      => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'label' => 'Sedang Dikerjakan', 'border' => 'border-blue-200'],
            'submitted'        => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Menunggu Review',   'border' => 'border-yellow-200'],
            'revision'         => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'Perlu Revisi',      'border' => 'border-red-200'],
            'cancel_requested' => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'label' => 'Req. Cancel',       'border' => 'border-amber-200'],
        ];
        // Tugas yang sedang dikerjakan (hanya 1, non-open)
        $taskInProgress = $myTasks->first(fn($t) => in_array($t->status, ['in_progress','submitted','revision','cancel_requested']));
        // Semua tugas open (belum mulai) — personal & pool yg sudah diklaim
        $openTasks = $myTasks->where('status', 'open')->values();
        // Apakah ada tugas yang sedang aktif (in_progress+) atau open — kalau ada, tidak bisa klaim pool baru
        $hasBlockingTask = $taskInProgress || $openTasks->isNotEmpty();
    @endphp

    <div>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                {{ ($selfAssignMode && $myTasks->isEmpty()) ? 'Buat Tugas Mandiri' : 'Tugas Aktif' }}
            </p>
            @if(!($selfAssignMode && $myTasks->isEmpty()))
            <a href="{{ route('work.index') }}" class="text-xs text-orange-500 font-medium">Semua Tugas →</a>
            @endif
        </div>

        {{-- Banner pengingat absen jika ada tugas aktif tapi belum absen masuk --}}
        @if($myTasks->isNotEmpty() && !$clockedIn)
        <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 mb-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-amber-700">Belum absen masuk hari ini</p>
                <p class="text-xs text-amber-600 mt-0.5">Absen dulu agar bisa memulai atau mengambil tugas.</p>
            </div>
            <a href="{{ route('attendance.clock-in') }}"
                class="text-xs font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-lg px-3 py-1.5 shrink-0 transition-colors">
                Absen
            </a>
        </div>
        @endif

        @if($myTasks->isEmpty() && $poolAssignments->isEmpty())
        {{-- Mode Mandiri: form buat tugas sendiri --}}
        @if($selfAssignMode && $clockedIn)
        <div class="bg-white rounded-2xl border border-orange-200 shadow-sm p-5" x-data="selfAssignForm()">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Buat Tugas Sendiri</p>
                    <p class="text-xs text-gray-400">Pilih pekerjaan yang ingin kamu kerjakan hari ini</p>
                </div>
            </div>

            <form action="{{ route('work.self-create') }}" method="POST" class="space-y-3">
                @csrf
                {{-- Kategori --}}
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Kategori Pakaian</label>
                    <select name="_kategori" x-model="kategori" @change="jenis = ''"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white" required>
                        <option value="">Pilih kategori...</option>
                        @foreach($garmentKategori as $kat)
                        <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis Pakaian --}}
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Jenis Pakaian</label>
                    <select name="garment_type_id" x-model="jenis"
                        :disabled="!kategori"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white disabled:opacity-50" required>
                        <option value="">Pilih jenis pakaian...</option>
                        @foreach($garmentByKategori as $kat => $items)
                        <template x-if="kategori === '{{ $kat }}'">
                            <template x-for="item in {{ $items->map(fn($i) => ['id' => $i->id, 'nama' => $i->contoh_pakaian])->values()->toJson() }}" :key="item.id">
                                <option :value="item.id" x-text="item.nama"></option>
                            </template>
                        </template>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis Pekerjaan --}}
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Jenis Pekerjaan</label>
                    <select name="work_type_id"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white" required>
                        <option value="">Pilih jenis pekerjaan...</option>
                        @foreach($workTypes as $wt)
                        <option value="{{ $wt->id }}">{{ $wt->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Target --}}
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Target Jumlah (item)</label>
                    <input type="number" name="target_jumlah" min="1" max="999" placeholder="Contoh: 10"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white" required>
                </div>

                {{-- Instruksi --}}
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Instruksi / Catatan <span class="font-normal text-gray-400">(opsional)</span></label>
                    <textarea name="instruksi" rows="3" maxlength="500"
                        placeholder="Detail pengerjaan, ukuran, warna, dll."
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-orange-300 focus:bg-white resize-none"></textarea>
                </div>

                <button type="submit"
                    class="w-full bg-orange-400 hover:bg-orange-500 text-white font-semibold py-3 rounded-xl text-sm transition-colors mt-1">
                    Mulai Kerjakan
                </button>
            </form>
        </div>

        @elseif($selfAssignMode && !$clockedIn)
        {{-- Mode mandiri aktif tapi belum absen --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600">Absen masuk dulu</p>
            <p class="text-xs text-gray-400 mt-1 mb-4">Kamu perlu absen masuk sebelum bisa membuat tugas mandiri.</p>
            <a href="{{ route('attendance.clock-in') }}"
                class="inline-block bg-orange-400 hover:bg-orange-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                Absen Masuk Sekarang
            </a>
        </div>

        @else
        {{-- Tidak ada tugas, mode mandiri nonaktif --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center" id="pool-empty-card">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">Belum ada tugas untukmu</p>
            <p class="text-xs text-gray-400 mt-1">Supervisor akan memberikan tugas segera</p>
            <p class="text-xs text-gray-300 mt-3">Memantau tugas baru setiap 3 detik...</p>
        </div>
        @endif

        @else

        {{-- 1. TUGAS SEDANG DIKERJAKAN (in_progress / submitted / revision / cancel_requested) --}}
        @if($taskInProgress)
        @php $wc = $statusColors[$taskInProgress->status] ?? $statusColors['open']; @endphp
        <div class="bg-white rounded-2xl border {{ $wc['border'] }} shadow-sm p-4 mb-3">
            <a href="{{ route('work.show', $taskInProgress) }}" class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 mb-1">
                            @if($taskInProgress->type === 'pool')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">Pool</span>
                            @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Personal</span>
                            @endif
                            <span class="inline-block text-xs px-2 py-0.5 rounded-full {{ $wc['bg'] }} {{ $wc['text'] }} font-medium">{{ $wc['label'] }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $taskInProgress->garmentType->contoh_pakaian }}</p>
                        <p class="text-xs text-gray-500">{{ $taskInProgress->workType->nama }} · {{ $taskInProgress->target_jumlah }} item</p>
                    </div>
                </div>
                <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif

        {{-- 2. ANTRIAN TUGAS OPEN (personal di atas, pool di bawah — sudah diurutkan dari route) --}}
        @if($openTasks->isNotEmpty())
        @if($taskInProgress)
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2" style="margin-top:0.25rem;">Antrian Berikutnya</p>
        @endif
        @foreach($openTasks as $task)
        @php
            $isPersonal = $task->type === 'personal';
            $canStart   = ! $taskInProgress; // bisa mulai kalau tidak ada yg sedang dikerjakan
        @endphp
        <div class="bg-white rounded-2xl border {{ $isPersonal ? 'border-orange-200' : 'border-yellow-200' }} shadow-sm p-4 mb-2">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-lg {{ $isPersonal ? 'bg-blue-50' : 'bg-yellow-50' }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 {{ $isPersonal ? 'text-blue-400' : 'text-yellow-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            @if($isPersonal)
                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Personal</span>
                            @else
                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">Pool</span>
                            @endif
                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500 font-medium">Belum Dimulai</span>
                        </div>
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $task->garmentType->contoh_pakaian }}</p>
                        <p class="text-xs text-gray-400">{{ $task->workType->nama }} · {{ $task->target_jumlah }} item</p>
                    </div>
                </div>
                @if($task->deadline)
                <span class="text-xs {{ $task->deadline->isPast() ? 'text-red-400 font-semibold' : 'text-gray-400' }} shrink-0">
                    {{ $task->deadline->format('d M') }}
                </span>
                @endif
            </div>
            @if($canStart)
            <form action="{{ route('work.start', $task) }}" method="POST">
                @csrf
                <button type="submit" style="width:100%;padding:0.5rem 1rem;background:{{ $isPersonal ? '#f97316' : '#eab308' }};color:#fff;border:none;border-radius:0.625rem;font-size:0.875rem;font-weight:600;cursor:pointer;">
                    ▶ Mulai Kerjakan
                </button>
            </form>
            @else
            <p class="text-xs text-gray-400 text-center py-1">Selesaikan tugas aktif dulu</p>
            @endif
        </div>
        @endforeach
        @endif

        {{-- 3. POOL REBUTAN — selalu tampil, tapi tidak bisa diklaim kalau ada personal --}}
        @if($poolAssignments->isNotEmpty())
        <div style="margin-top:0.75rem;">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Tugas Pool Tersedia</p>
            @if($hasPersonal)
            <div style="background:#fef9c3;border:1px solid #fde047;border-radius:0.75rem;padding:0.625rem 0.875rem;margin-bottom:0.625rem;">
                <p style="font-size:0.75rem;color:#854d0e;margin:0;">⚠️ Kamu punya tugas personal — selesaikan dulu baru bisa ambil tugas pool.</p>
            </div>
            @else
            <p class="text-xs text-gray-400 mb-2">
                <span class="font-semibold text-yellow-600">{{ $poolAssignments->count() }} tugas tersedia</span> — siapa cepat dia dapat!
            </p>
            @endif
            @foreach($poolAssignments as $pool)
            <div class="bg-white rounded-2xl border shadow-sm p-4 mb-3" style="border-color:{{ $hasPersonal ? '#e5e7eb' : '#fde68a' }};opacity:{{ $hasPersonal ? '0.7' : '1' }};">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $pool->garmentType->contoh_pakaian }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $pool->workType->nama }} · {{ $pool->target_jumlah }} item</p>
                        @if($pool->deadline)
                        <p class="text-xs mt-1 {{ $pool->deadline->isPast() ? 'text-red-400' : 'text-gray-400' }}">
                            Deadline: {{ $pool->deadline->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium shrink-0">Pool</span>
                </div>
                @if($hasPersonal)
                <div style="width:100%;padding:0.625rem 1rem;background:#d1d5db;color:#6b7280;border:none;border-radius:0.75rem;font-size:0.875rem;font-weight:600;text-align:center;cursor:not-allowed;">
                    🔒 Tidak Bisa Diambil
                </div>
                @else
                <form action="{{ route('work.claim', $pool) }}" method="POST">
                    @csrf
                    <button type="submit" style="width:100%;padding:0.625rem 1rem;background:#eab308;color:#fff;border:none;border-radius:0.75rem;font-size:0.875rem;font-weight:600;cursor:pointer;">
                        ⚡ Ambil Tugas
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @endif {{-- end: myTasks + poolAssignments empty check --}}
    </div>

    {{-- Auto-refresh kalau tidak ada tugas apapun --}}
    @if($myTasks->isEmpty() && $poolAssignments->isEmpty() && !$selfAssignMode)
    <script>
    (function() {
        setInterval(function() {
            fetch('/api/pool-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if ((data.count || 0) > 0) { window.location.reload(); }
                })
                .catch(function() {});
        }, 3000);
    })();
    </script>
    @endif

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('selfAssignForm', () => ({
            kategori: '',
            jenis: '',
        }));
    });
    </script>
    @endcan

    {{-- Menu Absensi --}}
    <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Absensi</p>
        <div class="grid grid-cols-2 gap-3">

            {{-- Absen Masuk / Pulang -- otomatis berubah --}}
            @if (!$clockedIn)
                {{-- Belum masuk → tampil Absen Masuk --}}
                <a href="{{ route('attendance.clock-in') }}"
                    class="bg-white rounded-2xl border border-orange-200 shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md hover:bg-orange-50 transition-all group col-span-2">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-semibold text-orange-600">Absen Masuk Sekarang</p>
                        <p class="text-xs text-gray-400 mt-0.5">Kamu belum absen hari ini</p>
                    </div>
                </a>
            @elseif (!$clockedOut)
                {{-- Sudah masuk, belum pulang → tampil Absen Pulang --}}
                <a href="{{ route('attendance.clock-out') }}"
                    class="bg-white rounded-2xl border border-orange-200 shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md hover:bg-orange-50 transition-all group col-span-2">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-semibold text-orange-600">Absen Pulang Sekarang</p>
                        <p class="text-xs text-gray-400 mt-0.5">Jangan lupa absen sebelum pulang</p>
                    </div>
                </a>
            @else
                {{-- Sudah masuk & pulang → selesai --}}
                <div class="bg-green-50 rounded-2xl border border-green-200 p-4 flex flex-col items-center gap-2 col-span-2">
                    <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-semibold text-green-600">Absensi Hari Ini Selesai</p>
                        <p class="text-xs text-gray-400 mt-0.5">Sampai jumpa besok!</p>
                    </div>
                </div>
            @endif

            <a href="{{ route('attendance.leave-request') }}"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md hover:border-orange-200 transition-all group">
                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Izin / Cuti</span>
            </a>

            <a href="{{ route('attendance.history') }}"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md hover:border-orange-200 transition-all group">
                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Riwayat</span>
            </a>
        </div>
    </div>

    {{-- Menu Penggajian --}}
    <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Penggajian</p>
        <a href="{{ route('payslips.index') }}"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 hover:shadow-md hover:border-orange-200 transition-all group">
            <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-700">Slip Gaji</span>
                <p class="text-xs text-gray-400">Lihat dan cetak slip gaji bulanan</p>
            </div>
        </a>
    </div>

</div>

<script>
    setInterval(() => {
        const el = document.getElementById('dashboard-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }, 1000);
</script>
@endsection
