@extends('layouts.guest')
@section('title', 'Buat Password - Atelier')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-800 text-center mb-1">Buat Password</h1>
    <p class="text-center text-sm text-gray-400 mb-6">Buat password untuk masuk ke akun Atelier kamu</p>

    <form method="POST" action="/reset-password" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ request('email') ?? old('email') }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent bg-gray-50">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
            <input type="password" name="password" id="password" required minlength="8"
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ulangi Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
        </div>
        <button type="submit"
            style="background-color:#F4A95A;"
            onmouseover="this.style.backgroundColor='#F09030'"
            onmouseout="this.style.backgroundColor='#F4A95A'"
            class="w-full text-white font-semibold py-2.5 rounded-lg transition-colors">
            Simpan Password & Masuk
        </button>
    </form>
</div>
@endsection

@push('scripts')
@if($errors->any())
<script>
Swal.fire({ icon: 'error', title: 'Gagal', text: '{{ $errors->first() }}', confirmButtonColor: '#F4A95A' });
</script>
@endif
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const pw = document.getElementById('password').value;
    const pw2 = document.getElementById('password_confirmation').value;
    if (pw !== pw2) {
        e.preventDefault();
        Swal.fire({ icon: "error", title: "Password Tidak Cocok", text: "Password dan konfirmasi password harus sama.", confirmButtonColor: "#F4A95A" });
    }
});
</script>
@endpush
