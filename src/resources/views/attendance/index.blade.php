@extends('layouts.app')
@section('title', 'Absensi - Atelier')

@section('content')
<div class="space-y-4">
    <h1 class="text-xl font-bold text-gray-800">Absensi</h1>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <a href="{{ route('attendance.clock-in') }}"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col items-center gap-3 hover:shadow-md hover:border-orange-200 transition-all group">
            <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-800">Absen Masuk</p>
                <p class="text-xs text-gray-400 mt-0.5">Clock in</p>
            </div>
        </a>

        <a href="{{ route('attendance.clock-out') }}"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col items-center gap-3 hover:shadow-md hover:border-orange-200 transition-all group">
            <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-800">Absen Pulang</p>
                <p class="text-xs text-gray-400 mt-0.5">Clock out</p>
            </div>
        </a>

        <a href="{{ route('attendance.leave-request') }}"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col items-center gap-3 hover:shadow-md hover:border-orange-200 transition-all group">
            <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-800">Izin / Cuti</p>
                <p class="text-xs text-gray-400 mt-0.5">Ajukan izin</p>
            </div>
        </a>

        <a href="{{ route('attendance.history') }}"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col items-center gap-3 hover:shadow-md hover:border-orange-200 transition-all group">
            <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-800">Riwayat</p>
                <p class="text-xs text-gray-400 mt-0.5">Histori absen</p>
            </div>
        </a>
    </div>
</div>
@endsection
