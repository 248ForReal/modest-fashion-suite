@extends('layouts.guest')
@section('title', 'Menunggu Persetujuan - Atelier')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">

    @if(session('just_registered'))
    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1 class="text-xl font-bold text-gray-800 mb-2">Pendaftaran Berhasil!</h1>
    <p class="text-gray-500 text-sm mb-2">Akun kamu sudah terdaftar dan sedang menunggu persetujuan admin.</p>
    <p class="text-gray-400 text-sm mb-6">Setelah disetujui, kamu akan menerima email berisi link untuk membuat password.</p>
    @else
    <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h1 class="text-xl font-bold text-gray-800 mb-2">Menunggu Persetujuan</h1>
    <p class="text-gray-400 text-sm mb-6">Akun kamu sedang menunggu persetujuan admin. Kamu akan mendapat notifikasi melalui email setelah akun disetujui.</p>
    @endif

    <form method="POST" action="/logout">
        @csrf
        <button type="submit" class="text-sm text-red-500 hover:underline">Keluar</button>
    </form>
</div>
@endsection

@push('scripts')
@if(session('swal_info'))
<script>
Swal.fire({ icon: "info", title: "Info", text: "{{ session('swal_info') }}", confirmButtonColor: "#F4A95A" });
</script>
@endif
@endpush
