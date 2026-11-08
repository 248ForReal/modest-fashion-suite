<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
    <div>
        <h2 class="text-lg font-bold text-gray-800">Ajukan Izin / Cuti</h2>
        <p class="text-sm text-gray-400 mt-0.5">Isi form di bawah untuk mengajukan izin atau cuti</p>
    </div>

    @if ($message)
        <div class="p-3 rounded-xl text-sm {{ $messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
            {{ $message }}
        </div>
    @endif

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe</label>
            <select wire:model="type"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent bg-white">
                <option value="leave">Cuti</option>
                <option value="permission">Izin</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal</label>
            <input type="date" wire:model="date"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent">
            @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Keterangan</label>
            <textarea wire:model="notes" rows="4"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent resize-none"
                placeholder="Tuliskan alasan izin atau cuti kamu..."></textarea>
            @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button wire:click="submit"
            class="w-full bg-orange-400 hover:bg-orange-500 active:bg-orange-600 text-white font-semibold py-3 px-4 rounded-xl transition-colors text-sm">
            Kirim Pengajuan
        </button>
    </div>
</div>
