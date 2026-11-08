@extends('layouts.guest')
@section('title', 'Lupa Password - Atelier')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <h1 class="text-2xl font-bold text-gray-800 text-center mb-1">Lupa Password</h1>
    <p class="text-center text-sm text-gray-400 mb-6">Masukkan email kamu, kami akan kirim link reset password.</p>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200">{{ session('status') }}</div>
    @endif

    <form method="POST" action="/forgot-password" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit"
            style="background-color:#F4A95A;"
            onmouseover="this.style.backgroundColor='#F09030'"
            onmouseout="this.style.backgroundColor='#F4A95A'"
            class="w-full text-white font-semibold py-2.5 rounded-lg transition-colors">
            Kirim Link Reset
        </button>
    </form>
    <p class="text-center text-sm text-gray-400 mt-6">
        <a href="/login" class="text-primary-600 hover:underline">← Kembali ke Login</a>
    </p>
</div>
@endsection
