@extends('layouts.app')
@section('title', 'Riwayat Absensi - Atelier')
@section('back', route('dashboard'))
@section('page_title', 'Riwayat Absensi')

@section('content')
<div class="space-y-4">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('attendance') }}" class="hover:text-gray-600">Absensi</a>
        <span>/</span>
        <span class="text-gray-700 font-medium">Riwayat</span>
    </div>

    <livewire:attendance.history />
</div>
@endsection
