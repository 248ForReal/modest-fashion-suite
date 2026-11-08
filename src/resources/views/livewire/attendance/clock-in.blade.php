<div class="space-y-4"
    x-data="clockInMap({ officeLat: {{ $officeLat }}, officeLon: {{ $officeLon }}, radius: {{ $radius }}, workStart: '{{ $workStart }}', allowOutside: {{ $allowOutside ? 'true' : 'false' }} })"
    x-init="getLocation()"
>
    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Absen Masuk</h2>
                <p class="text-sm text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-800" id="live-clock-in">{{ now()->format('H:i') }}</p>
                <div class="mt-1 px-2 py-0.5 rounded-lg text-xs font-medium border"
                    :class="timeStatus.bg + ' ' + timeStatus.color" x-text="timeStatus.label"></div>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Jam masuk kantor: <span class="font-medium text-gray-600">{{ $workStart }}</span></p>
    </div>

    @if ($message)
        <div class="p-4 rounded-2xl text-sm {{ $messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
            {{ $message }}
        </div>
    @endif

    {{-- Peta + Status Lokasi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div id="map-in" style="height:280px; background:#f3f4f6;" x-show="locStatus === 'ok'"></div>
        <div x-show="locStatus === 'idle' || locStatus === 'loading'"
            class="flex flex-col items-center justify-center gap-2 p-8 text-gray-400">
            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span class="text-sm">Mendeteksi lokasi...</span>
        </div>

        <div class="p-4 space-y-2">
            <div x-show="locStatus === 'ok'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full shrink-0" :class="inRadius ? 'bg-green-400' : 'bg-red-400'"></div>
                        <span class="text-sm font-semibold" :class="inRadius ? 'text-green-600' : 'text-red-500'"
                            x-text="inRadius ? '✓ Dalam area kantor' : '✗ Di luar area kantor'"></span>
                    </div>
                    <button type="button" @click="getLocation()"
                        class="text-xs text-orange-500 font-medium flex items-center gap-1 border border-orange-200 rounded-lg px-3 py-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <div class="bg-gray-50 rounded-xl p-2.5">
                        <p class="text-xs text-gray-400">📍 Jarak kamu</p>
                        <p class="text-sm font-bold text-gray-700 mt-0.5" x-text="distance >= 1000 ? (distance/1000).toFixed(1) + ' km' : distance + ' m'"></p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-2.5">
                        <p class="text-xs text-orange-400">🏢 Radius absen</p>
                        <p class="text-sm font-bold text-orange-600 mt-0.5" x-text="radius + ' m'"></p>
                    </div>
                </div>
            </div>
            <div x-show="locStatus === 'error'" class="flex items-center justify-between">
                <p class="text-sm text-red-500">Gagal mendapatkan lokasi. Pastikan GPS aktif.</p>
                <button type="button" @click="getLocation()"
                    class="text-xs text-orange-500 font-medium flex items-center gap-1 border border-orange-200 rounded-lg px-3 py-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <button type="button"
            :disabled="submitting"
            @click="submitClockIn($wire)"
            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-orange-500 active:bg-orange-600'"
            class="w-full bg-orange-400 text-white font-semibold py-3 px-4 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span x-text="submitting ? 'Memproses...' : 'Absen Masuk Sekarang'"></span>
        </button>
    </div>
</div>
