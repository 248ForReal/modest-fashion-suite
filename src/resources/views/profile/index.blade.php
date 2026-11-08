@extends('layouts.app')
@section('title', 'Profil - Atelier')
@section('back', route('dashboard'))
@section('page_title', 'Profil Saya')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <h1 class="text-xl font-bold text-gray-800">Profil Saya</h1>

    {{-- Info akun --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center text-2xl font-bold text-orange-500 shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                <p class="text-sm text-gray-400">{{ auth()->user()->email }}</p>
                <p class="text-xs text-orange-400 mt-0.5">{{ auth()->user()->getRoleNames()->join(', ') ?: '-' }}</p>
            </div>
        </div>

        <div class="border-t border-gray-50 pt-4 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Nama</span>
                <span class="text-gray-700 font-medium">{{ auth()->user()->name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Email</span>
                <span class="text-gray-700 font-medium">{{ auth()->user()->email }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">No. HP</span>
                <span class="text-gray-700 font-medium">{{ auth()->user()->phone ?? '-' }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Tanggal Lahir</span>
                <span class="text-gray-700 font-medium">
                    {{ auth()->user()->birth_date ? \Carbon\Carbon::parse(auth()->user()->birth_date)->translatedFormat('d F Y') : '-' }}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Status</span>
                <span class="font-medium {{ auth()->user()->status === 'active' ? 'text-green-600' : 'text-yellow-500' }}">
                    {{ ucfirst(auth()->user()->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Ganti Password --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h2 class="font-semibold text-gray-800">Ganti Password</h2>

        @if (session('status'))
            <div class="p-3 rounded-xl text-sm bg-green-50 text-green-700 border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Saat Ini</label>
                <input type="password" name="current_password" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent @error('current_password') border-red-400 @enderror">
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                <input type="password" name="password" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent @error('password') border-red-400 @enderror">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent">
            </div>
            <button type="submit"
                class="w-full bg-orange-400 hover:bg-orange-500 text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                Simpan Password Baru
            </button>
        </form>
    </div>
</div>
@endsection
