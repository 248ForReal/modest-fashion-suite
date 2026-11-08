@extends('layouts.app')
@section('title', 'Tugas Saya - Atelier')
@section('page_title', 'Tugas Saya')

@section('content')
<div class="space-y-5">

    {{-- Tugas Aktif --}}
    @php
        $statusColors = [
            'open'             => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'label' => 'Belum Dimulai'],
            'in_progress'      => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'label' => 'Sedang Dikerjakan'],
            'submitted'        => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Menunggu Review'],
            'revision'         => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'Perlu Revisi'],
            'cancel_requested' => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'label' => 'Req. Cancel'],
        ];
        $taskInProgress = $myTasks->first(fn($t) => in_array($t->status, ['in_progress','submitted','revision','cancel_requested']));
        $openTasks      = $myTasks->where('status', 'open')->values();
    @endphp

    <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tugas Aktif</p>

        @if($myTasks->isEmpty() && $poolAssignments->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center mb-4">
            <p class="text-sm text-gray-400">Tidak ada tugas aktif saat ini</p>
            @if($cooldown['in_cooldown'])
            <p class="text-xs text-amber-500 mt-2 font-medium">⏳ Cooldown aktif</p>
            @endif
        </div>
        @endif

        {{-- Tugas yang sedang dikerjakan (in_progress / submitted / revision / cancel_req) --}}
        @if($taskInProgress)
        @php $c = $statusColors[$taskInProgress->status] ?? $statusColors['open']; @endphp
        <a href="{{ route('work.show', $taskInProgress) }}"
           class="block bg-white rounded-2xl border border-orange-200 shadow-sm p-4 hover:shadow-md transition-all mb-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $c['bg'] }} {{ $c['text'] }}">{{ $c['label'] }}</span>
                        @if($taskInProgress->type === 'pool')
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium bg-yellow-100 text-yellow-700">Pool</span>
                        @else
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium bg-blue-100 text-blue-700">Personal</span>
                        @endif
                    </div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $taskInProgress->garmentType->contoh_pakaian }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $taskInProgress->workType->nama }}</p>
                    <div class="mt-2 flex items-center gap-4 text-xs text-gray-400">
                        <span>Target: <b class="text-gray-700">{{ $taskInProgress->target_jumlah }} item</b></span>
                        @if($taskInProgress->deadline)
                        <span>Deadline: <b class="text-gray-700">{{ $taskInProgress->deadline->format('d M Y') }}</b></span>
                        @endif
                    </div>
                    @if($taskInProgress->status === 'revision' && $taskInProgress->latestSubmission?->review)
                    <div class="mt-2 bg-red-50 border border-red-100 rounded-xl p-2 text-xs text-red-600">
                        <b>Catatan revisi:</b> {{ $taskInProgress->latestSubmission->review->catatan_review ?? 'Periksa hasil dan submit ulang.' }}
                    </div>
                    @endif
                </div>
                <svg class="w-5 h-5 text-gray-300 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @endif

        {{-- Antrian tugas open (belum dimulai) --}}
        @if($openTasks->isNotEmpty())
        @if($taskInProgress)
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Antrian Berikutnya</p>
        @endif
        @foreach($openTasks as $task)
        <a href="{{ route('work.show', $task) }}"
           class="block bg-white rounded-2xl border shadow-sm p-4 mb-2 hover:shadow-md transition-all"
           style="border-color:{{ $task->type === 'personal' ? '#fed7aa' : '#fde68a' }};">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-600">Belum Dimulai</span>
                        @if($task->type === 'pool')
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium bg-yellow-100 text-yellow-700">Pool</span>
                        @else
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium bg-blue-100 text-blue-700">Personal</span>
                        @endif
                    </div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $task->garmentType->contoh_pakaian }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $task->workType->nama }}</p>
                    <div class="mt-2 flex items-center gap-4 text-xs text-gray-400">
                        <span>Target: <b class="text-gray-700">{{ $task->target_jumlah }} item</b></span>
                        @if($task->deadline)
                        <span>Deadline: <b class="{{ $task->deadline->isPast() ? 'text-red-500' : 'text-gray-700' }}">{{ $task->deadline->format('d M Y') }}</b></span>
                        @endif
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-300 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @endforeach
        @endif

        {{-- Cooldown Banner --}}
        @if($cooldown['in_cooldown'])
        <div id="cooldown-banner" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-amber-700">Masa Cooldown Aktif</p>
                <p class="text-xs text-amber-600 mt-0.5">Kamu bisa mengambil tugas pool baru setelah cooldown selesai.</p>
                <p class="text-xs text-amber-500 mt-1">
                    Sisa waktu: <span id="cooldown-timer" class="font-semibold tabular-nums">{{ gmdate('i:s', $cooldown['remaining_seconds']) }}</span>
                </p>
            </div>
        </div>
        <script>
        (function () {
            var remaining = {{ $cooldown['remaining_seconds'] }};
            var el = document.getElementById('cooldown-timer');
            if (!el || remaining <= 0) return;
            var interval = setInterval(function () {
                remaining--;
                if (remaining <= 0) { clearInterval(interval); location.reload(); return; }
                var m = Math.floor(remaining / 60);
                var s = remaining % 60;
                el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }, 1000);
        })();
        </script>
        @endif

        {{-- Tugas Pool — selalu tampil, tapi terkunci kalau ada personal --}}
        @if(!$cooldown['in_cooldown'] && $poolAssignments->isNotEmpty())
        <div style="margin-top:0.75rem;">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                Tugas Pool Tersedia
                <span class="ml-1 bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full text-xs font-medium">{{ $poolAssignments->count() }}</span>
            </p>
            @if($hasPersonal)
            <div style="background:#fef9c3;border:1px solid #fde047;border-radius:0.75rem;padding:0.625rem 0.875rem;margin-bottom:0.75rem;">
                <p style="font-size:0.75rem;color:#854d0e;margin:0;">⚠️ Kamu punya tugas personal — selesaikan dulu baru bisa ambil tugas pool.</p>
            </div>
            @endif
            @foreach($poolAssignments as $pool)
            <div class="bg-white rounded-2xl border shadow-sm p-4 mb-3"
                 style="border-color:{{ $hasPersonal ? '#e5e7eb' : '#fde68a' }};opacity:{{ $hasPersonal ? '0.65' : '1' }};">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0 flex-1">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">Pool — Rebutan</span>
                        <p class="font-semibold text-gray-800 text-sm mt-1">{{ $pool->garmentType->contoh_pakaian }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $pool->workType->nama }}</p>
                        <div class="mt-1.5 flex items-center gap-4 text-xs text-gray-400">
                            <span>Target: <b class="text-gray-700">{{ $pool->target_jumlah }} item</b></span>
                            @if($pool->deadline)
                            <span>Deadline: <b class="{{ $pool->deadline->isPast() ? 'text-red-500' : 'text-gray-700' }}">{{ $pool->deadline->format('d M Y') }}</b></span>
                            @endif
                        </div>
                        @if($pool->instruksi)
                        <p class="text-xs text-gray-400 mt-1 truncate">{{ $pool->instruksi }}</p>
                        @endif
                    </div>
                </div>
                @if($hasPersonal)
                <div style="width:100%;padding:0.5rem 1rem;background:#d1d5db;color:#6b7280;border:none;border-radius:0.75rem;font-size:0.875rem;font-weight:600;text-align:center;cursor:not-allowed;">
                    🔒 Tidak Bisa Diambil
                </div>
                @else
                <form action="{{ route('work.claim', $pool) }}" method="POST">
                    @csrf
                    <button type="submit" style="width:100%;padding:0.5rem 1rem;background:#eab308;color:#fff;border:none;border-radius:0.75rem;font-size:0.875rem;font-weight:600;cursor:pointer;">
                        ⚡ Ambil Tugas
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @elseif(!$cooldown['in_cooldown'] && $myTasks->isEmpty())
        <div class="text-center py-2 text-xs text-gray-400">Tidak ada tugas pool yang tersedia.</div>
        @endif
    </div>

    {{-- Riwayat --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Riwayat Tugas</p>
            <a href="{{ route('work.rekap') }}" class="text-xs text-orange-500 font-medium">Lihat Rekap →</a>
        </div>

        @forelse($history as $item)
        <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-3">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5
                    {{ $item->status === 'approved' ? 'bg-green-100' : 'bg-red-100' }}">
                    @if($item->status === 'approved')
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    @else
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $item->garmentType->contoh_pakaian }}</p>
                    <p class="text-xs text-gray-400">{{ $item->workType->nama }}</p>
                    <div class="mt-1 flex items-center gap-3 text-xs">
                        @if($item->latestSubmission)
                        <span class="text-gray-500">{{ $item->latestSubmission->jumlah_selesai }}/{{ $item->target_jumlah }} item</span>
                        <span class="font-medium {{ $item->status === 'approved' ? 'text-orange-500' : 'text-gray-400' }}">
                            {{ number_format($item->latestSubmission->nilai_poin, 2) }} poin
                        </span>
                        @if($item->latestSubmission->review?->skor_kualitas)
                        <span class="text-yellow-500">★ {{ $item->latestSubmission->review->skor_kualitas }}/5</span>
                        @endif
                        @endif
                    </div>
                </div>
                <span class="text-xs text-gray-300">{{ $item->updated_at->format('d M') }}</span>
            </div>
        </div>
        @empty
        <div class="text-center py-6 text-sm text-gray-400">Belum ada riwayat tugas</div>
        @endforelse

        {{ $history->links() }}
    </div>

</div>
@endsection
