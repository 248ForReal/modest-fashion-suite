<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
    <h2 class="text-lg font-bold text-gray-800">Riwayat Absensi</h2>

    {{-- Filter --}}
    <div class="flex flex-col sm:flex-row gap-2">
        <select wire:model.live="filterType"
            class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 bg-white">
            <option value="">Semua Tipe</option>
            <option value="clock_in">Masuk</option>
            <option value="clock_out">Keluar</option>
            <option value="leave">Cuti</option>
            <option value="permission">Izin</option>
        </select>
        <input type="month" wire:model.live="filterMonth"
            class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
    </div>

    {{-- Desktop table --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 rounded-l-xl">Tanggal & Waktu</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Jarak</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 rounded-r-xl">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($attendances as $item)
                    @php
                        $labels = [
                            'clock_in'   => ['Masuk',  'bg-orange-100 text-orange-700'],
                            'clock_out'  => ['Keluar', 'bg-gray-100 text-gray-700'],
                            'leave'      => ['Cuti',   'bg-yellow-100 text-yellow-700'],
                            'permission' => ['Izin',   'bg-purple-100 text-purple-700'],
                        ];
                        [$label, $class] = $labels[$item->type] ?? [$item->type, ''];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $item->noted_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $class }}">{{ $label }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $item->distance_from_office !== null ? $item->distance_from_office . ' m' : '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($item->is_valid)
                                <span class="text-green-600 font-medium text-xs">✓ Valid</span>
                            @else
                                <span class="text-red-500 font-medium text-xs">✗ Di luar radius</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $item->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">Belum ada data absen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="sm:hidden space-y-3">
        @forelse ($attendances as $item)
            @php
                $labels = [
                    'clock_in'   => ['Masuk',  'bg-orange-100 text-orange-700'],
                    'clock_out'  => ['Keluar', 'bg-gray-100 text-gray-700'],
                    'leave'      => ['Cuti',   'bg-yellow-100 text-yellow-700'],
                    'permission' => ['Izin',   'bg-purple-100 text-purple-700'],
                ];
                [$label, $class] = $labels[$item->type] ?? [$item->type, ''];
            @endphp
            <div class="border border-gray-100 rounded-xl p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $class }}">{{ $label }}</span>
                    @if ($item->is_valid)
                        <span class="text-green-600 text-xs font-medium">✓ Valid</span>
                    @else
                        <span class="text-red-500 text-xs font-medium">✗ Di luar radius</span>
                    @endif
                </div>
                <p class="text-sm font-medium text-gray-700">{{ $item->noted_at->format('d M Y, H:i') }}</p>
                <div class="flex gap-4 text-xs text-gray-400">
                    <span>Jarak: {{ $item->distance_from_office !== null ? $item->distance_from_office . ' m' : '-' }}</span>
                    @if($item->notes) <span>{{ $item->notes }}</span> @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-400 text-sm">Belum ada data absen.</div>
        @endforelse
    </div>

    <div>{{ $attendances->links() }}</div>
</div>
