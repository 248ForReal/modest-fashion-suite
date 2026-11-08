<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Atelier')</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="/css/sweetalert2.min.css">
    <script src="/js/sweetalert2.min.js"></script>
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen">

<nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center gap-3">

        {{-- Back button (opsional, diisi tiap halaman) --}}
        @hasSection('back')
            <a href="@yield('back')" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors shrink-0">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        {{-- Logo --}}
        <a href="{{ auth()->user()->hasAnyRole(['super_admin','admin']) ? '/admin' : route('dashboard') }}" class="flex items-center gap-2 shrink-0">
            <img src="/images/logo.svg" alt="Atelier" class="h-8 w-auto">
        </a>

        {{-- Page title (opsional) --}}
        @hasSection('page_title')
            <span class="text-sm font-semibold text-gray-700 truncate">@yield('page_title')</span>
        @endif

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Desktop nav links --}}
        <div class="hidden sm:flex items-center gap-1">
            <a href="{{ auth()->user()->hasAnyRole(['super_admin','admin']) ? '/admin' : route('dashboard') }}"
                class="flex items-center gap-1.5 text-sm px-2.5 py-1.5 rounded-lg transition-colors
                    {{ request()->routeIs('dashboard') ? 'text-orange-500 bg-orange-50 font-medium' : 'text-gray-500 hover:text-orange-500 hover:bg-orange-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            @can('work.view_own')
            <a href="{{ route('work.index') }}"
                class="flex items-center gap-1.5 text-sm px-2.5 py-1.5 rounded-lg transition-colors
                    {{ request()->routeIs('work.*') ? 'text-orange-500 bg-orange-50 font-medium' : 'text-gray-500 hover:text-orange-500 hover:bg-orange-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Tugas
            </a>
            @endcan
            @php $userRole = auth()->user()?->roles->first()?->name; @endphp
            @if($userRole && \App\Filament\Pages\WorkSettings::isUserModuleEnabled('cetak_barcode') && \App\Filament\Pages\WorkSettings::canAccessUserModule($userRole, 'cetak_barcode'))
            <a href="{{ route('user.cetak-barcode') }}"
                class="flex items-center gap-1.5 text-sm px-2.5 py-1.5 rounded-lg transition-colors
                    {{ request()->routeIs('user.cetak-barcode') ? 'text-orange-500 bg-orange-50 font-medium' : 'text-gray-500 hover:text-orange-500 hover:bg-orange-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Barcode
            </a>
            @endif
            @if($userRole && \App\Filament\Pages\WorkSettings::isUserModuleEnabled('scan_barcode') && \App\Filament\Pages\WorkSettings::canAccessUserModule($userRole, 'scan_barcode'))
            <a href="{{ route('user.scan-barcode') }}"
                class="flex items-center gap-1.5 text-sm px-2.5 py-1.5 rounded-lg transition-colors
                    {{ request()->routeIs('user.scan-barcode') ? 'text-orange-500 bg-orange-50 font-medium' : 'text-gray-500 hover:text-orange-500 hover:bg-orange-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Scan Barcode
            </a>
            @endif
            @if($userRole && \App\Filament\Pages\WorkSettings::isUserModuleEnabled('distribusi') && \App\Filament\Pages\WorkSettings::canAccessUserModule($userRole, 'distribusi'))
            <a href="{{ route('user.distribusi') }}"
                class="flex items-center gap-1.5 text-sm px-2.5 py-1.5 rounded-lg transition-colors
                    {{ request()->routeIs('user.distribusi') ? 'text-orange-500 bg-orange-50 font-medium' : 'text-gray-500 hover:text-orange-500 hover:bg-orange-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Distribusi
            </a>
            @endif
        </div>

        {{-- Profile dropdown --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open"
                class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-gray-50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-sm font-bold text-orange-500 shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name }}</span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-lg border border-gray-100 py-2 z-50">
                <a href="{{ auth()->user()->hasAnyRole(['super_admin','admin']) ? '/admin' : route('dashboard') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 sm:hidden">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('profile') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profil Saya
                </a>
                @can('work.view_own')
                <a href="{{ route('work.index') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('work.*') ? 'text-orange-500 bg-orange-50' : '' }}">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Tugas Saya
                </a>
                <a href="{{ route('work.rekap') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Rekap Pekerjaan
                </a>
                @endcan
                @if($userRole && \App\Filament\Pages\WorkSettings::isUserModuleEnabled('cetak_barcode') && \App\Filament\Pages\WorkSettings::canAccessUserModule($userRole, 'cetak_barcode'))
                <a href="{{ route('user.cetak-barcode') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('user.cetak-barcode') ? 'text-orange-500 bg-orange-50' : '' }}">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Barcode
                </a>
                @endif
                @if($userRole && \App\Filament\Pages\WorkSettings::isUserModuleEnabled('scan_barcode') && \App\Filament\Pages\WorkSettings::canAccessUserModule($userRole, 'scan_barcode'))
                <a href="{{ route('user.scan-barcode') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('user.scan-barcode') ? 'text-orange-500 bg-orange-50' : '' }}">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    Scan Barcode
                </a>
                @endif
                @if($userRole && \App\Filament\Pages\WorkSettings::isUserModuleEnabled('distribusi') && \App\Filament\Pages\WorkSettings::canAccessUserModule($userRole, 'distribusi'))
                <a href="{{ route('user.distribusi') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('user.distribusi') ? 'text-orange-500 bg-orange-50' : '' }}">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Distribusi
                </a>
                @endif
                <div class="border-t border-gray-50 my-1"></div>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-4 py-6">
    @yield('content')
</main>

@livewireScripts
@stack('scripts')
<script>
@if(session('success'))
Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), confirmButtonColor: '#F4A95A', timer: 3000, timerProgressBar: true });
@endif
@if(session('error'))
Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonColor: '#F4A95A' });
@endif
@if(session('swal_success'))
Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('swal_success')), confirmButtonColor: '#F4A95A', timer: 3000, timerProgressBar: true });
@endif
@if(session('swal_error'))
Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('swal_error')), confirmButtonColor: '#F4A95A' });
@endif
@if(session('swal_info'))
Swal.fire({ icon: 'info', title: 'Info', text: @json(session('swal_info')), confirmButtonColor: '#F4A95A' });
@endif
</script>
</body>
</html>
