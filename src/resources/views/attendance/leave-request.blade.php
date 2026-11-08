@extends('layouts.app')
@section('title', 'Izin / Cuti - Atelier')
@section('back', route('dashboard'))
@section('page_title', 'Izin / Cuti')

@section('content')
<div class="max-w-md mx-auto space-y-4">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('attendance') }}" class="hover:text-gray-600">Absensi</a>
        <span>/</span>
        <span class="text-gray-700 font-medium">Izin / Cuti</span>
    </div>

    <livewire:attendance.leave-request />
</div>
@endsection
