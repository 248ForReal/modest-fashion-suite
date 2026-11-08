@extends('layouts.guest')
@section('title', 'Masuk - Atelier')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <h1 class="text-2xl font-bold text-gray-800 text-center mb-1">Selamat Datang</h1>
    <p class="text-sm text-gray-400 text-center mb-6">Masuk ke akun Atelier Anda</p>

    <form method="POST" action="/login" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
        </div>
        <div class="flex items-center justify-end">
            <a href="/forgot-password" class="text-sm text-primary-600 hover:underline">Lupa password?</a>
        </div>
        <button type="submit"
            style="background-color:#F4A95A;"
            onmouseover="this.style.backgroundColor='#F09030'"
            onmouseout="this.style.backgroundColor='#F4A95A'"
            class="w-full text-white font-semibold py-2.5 rounded-lg transition-colors">
            Masuk
        </button>
    </form>

    <div class="my-5 flex items-center gap-3">
        <div class="flex-1 border-t border-gray-100"></div>
        <span class="text-xs text-gray-400">atau</span>
        <div class="flex-1 border-t border-gray-100"></div>
    </div>

    <a href="{{ route('auth.google.login') }}"
        class="w-full flex items-center justify-center gap-2 border border-gray-200 rounded-lg py-2.5 text-sm font-medium hover:bg-gray-50 transition-colors">
        <img src="https://www.google.com/favicon.ico" class="w-4 h-4">
        Masuk dengan Google
    </a>

    <p class="text-center text-sm text-gray-400 mt-6">
        Belum punya akun? <a href="/register" class="text-primary-600 hover:underline font-medium">Daftar</a>
    </p>
</div>
@endsection

@push('scripts')
@if(session('status'))
<script>
Swal.fire({ icon: "success", title: "Berhasil", text: "{{ session('status') }}", confirmButtonColor: "#F4A95A" });
</script>
@endif
@if(session('swal_info'))
<script>
Swal.fire({ icon: "info", title: "Perhatian", text: "{{ session('swal_info') }}", confirmButtonColor: "#F4A95A" });
</script>
@endif
@endpush
