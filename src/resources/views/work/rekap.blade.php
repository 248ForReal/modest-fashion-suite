@extends('layouts.app')
@section('title', 'Rekap Tugas - Atelier')
@section('back', route('work.index'))
@section('page_title', 'Rekap Tugas')

@section('content')
<div class="space-y-5">

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-500">{{ number_format($totalPoin, 1) }}</p>
            <p class="text-xs text-gray-400 mt-1">Total Poin</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-yellow-500">
                {{ $avgKualitas ? number_format($avgKualitas, 1) : '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Rata-rata Kualitas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-500">{{ $stats['approved'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Tugas Selesai</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-400">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Total Tugas</p>
        </div>
    </div>

    {{-- Semua Riwayat --}}
    <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Semua Riwayat</p>

        @forelse($history as $item)
        @php
            $statusColors = [
                'open'        => 'text-gray-400',
                'in_progress' => 'text-blue-500',
                'submitted'   => 'text-yellow-500',
                'revision'    => 'text-red-500',
                'approved'    => 'text-green-600',
                'rejected'    => 'text-red-500',
            ];
            $statusLabels = [
                'open'        => 'Open',
                'in_progress' => 'Dikerjakan',
                'submitted'   => 'Menunggu Review',
                'revision'    => 'Revisi',
                'approved'    => 'Disetujui',
                'rejected'    => 'Ditolak',
            ];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-3">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item->garmentType->contoh_pakaian }}</p>
                    <p class="text-xs text-gray-400">{{ $item->workType->nama }}</p>
                    <div class="mt-1.5 flex items-center gap-3 text-xs">
                        <span class="{{ $statusColors[$item->status] ?? 'text-gray-400' }} font-medium">
                            {{ $statusLabels[$item->status] ?? $item->status }}
                        </span>
                        @if($item->latestSubmission)
                        <span class="text-gray-400">{{ $item->latestSubmission->jumlah_selesai }}/{{ $item->target_jumlah }} item</span>
                        @if($item->status === 'approved')
                        <span class="text-orange-500 font-medium">{{ number_format($item->latestSubmission->nilai_poin, 2) }} poin</span>
                        @endif
                        @if($item->latestSubmission->review?->skor_kualitas)
                        <span class="text-yellow-400">★ {{ $item->latestSubmission->review->skor_kualitas }}</span>
                        @endif
                        @endif
                    </div>
                </div>
                <span class="text-xs text-gray-300 shrink-0">{{ $item->updated_at->format('d M Y') }}</span>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-sm text-gray-400">Belum ada riwayat tugas</div>
        @endforelse

        {{ $history->links() }}
    </div>

</div>
@endsection
