@extends('layouts.guest')
@section('title', 'Daftar - Atelier')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <h1 class="text-2xl font-bold text-gray-800 text-center mb-1">Daftar Akun</h1>
    <p class="text-center text-sm text-gray-400 mb-6">Isi data kamu, lalu lanjut verifikasi dengan Google</p>

    <form method="POST" action="/register" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx"
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
            <input type="date" name="birth_date" value="{{ old('birth_date') }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent">
            @error('birth_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit"
            style="background-color:#F4A95A;"
            onmouseover="this.style.backgroundColor='#F09030'"
            onmouseout="this.style.backgroundColor='#F4A95A'"
            class="w-full flex items-center justify-center gap-2 text-white font-semibold py-2.5 rounded-lg transition-colors">
            <img src="https://www.google.com/favicon.ico" class="w-4 h-4"> Lanjut dengan Google
        </button>
    </form>

    <p class="text-center text-sm text-gray-400 mt-6">
        Sudah punya akun? <a href="/login" class="text-primary-600 hover:underline font-medium">Masuk</a>
    </p>
</div>
@endsection

@push('scripts')
@if(session('error'))
<script>
Swal.fire({ icon: "error", title: "Gagal", text: "{{ session('error') }}", confirmButtonColor: "#F4A95A" });
</script>
@endif
@if($errors->any())
<script>
Swal.fire({ icon: "error", title: "Periksa Form", text: "{{ $errors->first() }}", confirmButtonColor: "#F4A95A" });
</script>
@endif
@endpush
