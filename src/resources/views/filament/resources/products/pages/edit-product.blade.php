<x-filament-panels::page>

{{-- Form utama produk (default Filament) --}}
<x-filament-panels::form wire:submit="save">
    {{ $this->form }}
    <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
</x-filament-panels::form>

{{-- Section unit produk --}}
@php
    $units        = $this->getUnitsForProduct();
    $warehouseOpts = $this->getWarehouseOptions();
    $selected     = $unitCfg['selected_units'] ?? [];
    $gudangTujuan = $unitCfg['gudang_tujuan_id'] ?? null;
    $bisaPindah   = count(array_filter($units, fn($u) => $u['bisa_pindah']));

    $badgeColors = [
        'success' => ['bg'=>'#dcfce7','color'=>'#15803d'],
        'warning' => ['bg'=>'#fef9c3','color'=>'#a16207'],
        'info'    => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
        'danger'  => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
        'primary' => ['bg'=>'#ffedd5','color'=>'#c2410c'],
        'gray'    => ['bg'=>'#f3f4f6','color'=>'#374151'],
    ];
@endphp

@if(count($units) > 0)
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05); margin-top:8px;">

    {{-- Header section --}}
    <div style="padding:16px 20px; background:#fafafa; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
        <div>
            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Unit Produk</p>
            <p style="font-size:12px; color:#6b7280; margin:2px 0 0;">{{ count($units) }} unit terdaftar — klik untuk pilih unit yang ingin dipindah gudang</p>
        </div>
        @if($bisaPindah > 0 && count($warehouseOpts) > 0)
        <div style="display:flex; gap:6px;">
            <button wire:click="pilihSemuaUnit" type="button" style="font-size:12px; color:#ea580c; background:none; border:1px solid #ea580c; border-radius:6px; padding:5px 12px; cursor:pointer; font-weight:500;">Pilih Semua</button>
            <button wire:click="batalSemuaUnit" type="button" style="font-size:12px; color:#6b7280; background:none; border:1px solid #d1d5db; border-radius:6px; padding:5px 12px; cursor:pointer; font-weight:500;">Batal Pilih</button>
        </div>
        @endif
    </div>

    {{-- Grid unit --}}
    <div style="padding:16px 20px; {{ count($selected) > 0 ? 'border-bottom:1px solid #fed7aa;' : '' }}">
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            @foreach($units as $u)
            @php
                $bc      = $badgeColors[$u['status_color']] ?? $badgeColors['gray'];
                $dipilih = in_array($u['unit_code'], $selected);
            @endphp
            <div wire:click="{{ $u['bisa_pindah'] ? 'toggleUnitPindah(\'' . $u['unit_code'] . '\')' : '' }}"
                style="border:2px solid {{ $dipilih ? '#ea580c' : $bc['color'] . '33' }}; background:{{ $dipilih ? '#fff7ed' : $bc['bg'] }}; border-radius:8px; padding:7px 12px; min-width:170px; {{ $u['bisa_pindah'] ? 'cursor:pointer;' : 'opacity:.7;' }} transition:all .1s; position:relative;">
                @if($u['bisa_pindah'])
                <div style="position:absolute; top:5px; right:7px; width:15px; height:15px; border-radius:3px; border:1.5px solid {{ $dipilih ? '#ea580c' : '#d1d5db' }}; background:{{ $dipilih ? '#ea580c' : '#fff' }}; display:flex; align-items:center; justify-content:center;">
                    @if($dipilih)<svg style="width:9px;height:9px;" fill="none" viewBox="0 0 12 12" stroke="#fff" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>@endif
                </div>
                @endif
                <p style="font-size:11px; font-family:monospace; font-weight:700; color:#111827; margin:0; padding-right:{{ $u['bisa_pindah'] ? '18px' : '0' }};">{{ $u['unit_code'] }}</p>
                <div style="display:flex; align-items:center; gap:6px; margin-top:3px;">
                    <span style="font-size:10px; font-weight:600; color:{{ $bc['color'] }};">{{ $u['status_label'] }}</span>
                    <span style="font-size:10px; color:#9ca3af;">·</span>
                    <span style="font-size:10px; color:#6b7280;">{{ $u['gudang'] }}</span>
                </div>
                <p style="font-size:9px; color:#9ca3af; margin:1px 0 0;">{{ $u['updated_at'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Panel pindah unit --}}
    @if(count($selected) > 0)
    <div style="padding:16px 20px; background:#fff7ed;">
        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
            <div>
                <p style="font-size:12px; font-weight:600; color:#92400e; margin:0 0 4px;">{{ count($selected) }} unit dipilih</p>
            </div>
            <div style="flex:1; min-width:200px;">
                <label style="display:block; font-size:12px; font-weight:500; color:#92400e; margin-bottom:4px;">Pindah ke Gudang</label>
                <select wire:model="unitCfg.gudang_tujuan_id" style="width:100%; border:1px solid #fed7aa; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff;">
                    <option value="">-- Pilih gudang tujuan --</option>
                    @foreach($warehouseOpts as $wid => $wnama)
                    <option value="{{ $wid }}" @selected($gudangTujuan == $wid)>{{ $wnama }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="pindahkanUnit" type="button"
                style="background:{{ $gudangTujuan ? '#ea580c' : '#d1d5db' }}; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:600; cursor:{{ $gudangTujuan ? 'pointer' : 'not-allowed' }}; white-space:nowrap;">
                Pindahkan {{ count($selected) }} Unit
            </button>
        </div>
    </div>
    @endif

</div>
@endif

</x-filament-panels::page>
