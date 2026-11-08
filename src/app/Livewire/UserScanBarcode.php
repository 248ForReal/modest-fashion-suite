<?php

namespace App\Livewire;

use App\Filament\Pages\WorkSettings;
use App\Models\InventoryTransaction;
use App\Models\ProductUnit;
use App\Models\ProductUnitLog;
use App\Models\Warehouse;
use Livewire\Component;

class UserScanBarcode extends Component
{
    public array $cfg = [
        'scan_input'     => '',
        'panel_tab'      => 'status',
        'aksi'           => '',
        'posisi_input'   => '',
        'gudang_tujuan'  => null,
        'pindah_catatan' => '',
        'warehouse_id'   => null,
        'catatan'        => '',
        'success'        => false,
        'success_msg'    => '',
    ];

    public ?array $unit = null;

    private static array $transitions = [
        'stok'         => ['dipesan'],
        'dipesan'      => ['dikirim', 'stok'],
        'dikirim'      => ['sampai'],
        'sampai'       => ['selesai', 'dikembalikan'],
        'selesai'      => [],
        'dikembalikan' => ['stok'],
    ];

    public function getWarehouseOptions(): array
    {
        return Warehouse::active()->orderBy('nama')->pluck('nama', 'id')->toArray();
    }

    public function getAllowedTransitions(string $currentStatus): array
    {
        return self::$transitions[$currentStatus] ?? [];
    }

    public function getNextStatuses(): array
    {
        if (!$this->unit) return [];
        $allowed = $this->getAllowedTransitions($this->unit['status']);
        return array_intersect_key(ProductUnit::STATUSES, array_flip($allowed));
    }

    public function getUnitLogs(): array
    {
        if (!$this->unit) return [];

        return ProductUnitLog::with(['user', 'warehouse'])
            ->where('product_unit_id', $this->unit['id'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($log) => [
                'dari'    => ProductUnit::STATUSES[$log->status_dari] ?? $log->status_dari ?? '-',
                'ke'      => ProductUnit::STATUSES[$log->status_ke] ?? $log->status_ke,
                'ke_raw'  => $log->status_ke,
                'gudang'  => $log->warehouse?->nama ?? '-',
                'user'    => $log->user?->name ?? 'Sistem',
                'catatan' => $log->catatan,
                'waktu'   => $log->created_at->format('d M Y H:i'),
            ])
            ->toArray();
    }

    public function scan(): void
    {
        $this->unit                  = null;
        $this->cfg['aksi']           = '';
        $this->cfg['posisi_input']   = '';
        $this->cfg['gudang_tujuan']  = null;
        $this->cfg['pindah_catatan'] = '';
        $this->cfg['success']        = false;
        $this->cfg['success_msg']    = '';

        $code = trim($this->cfg['scan_input'] ?? '');
        if (!$code) return;

        $unit = ProductUnit::with(['product.garmentCategory', 'warehouse'])
            ->where('unit_code', $code)
            ->orWhere('unit_code', 'LIKE', '%' . $code)
            ->first();

        if (!$unit) {
            $this->cfg['success']     = false;
            $this->cfg['success_msg'] = '';
            session()->flash('error', "Unit tidak ditemukan: {$code}");
            return;
        }

        $this->unit = [
            'id'           => $unit->id,
            'unit_code'    => $unit->unit_code,
            'status'       => $unit->status,
            'status_label' => ProductUnit::STATUSES[$unit->status] ?? $unit->status,
            'status_color' => ProductUnit::STATUS_COLORS[$unit->status] ?? 'gray',
            'product_nama' => $unit->product->nama,
            'product_code' => $unit->product->product_code,
            'kategori'     => $unit->product->garmentCategory?->nama,
            'harga'        => 'Rp ' . number_format($unit->product->harga, 0, ',', '.'),
            'gudang'       => $unit->warehouse?->nama ?? '-',
            'warehouse_id' => $unit->warehouse_id,
            'posisi'       => $unit->posisi ?? '',
        ];

        $this->cfg['warehouse_id'] = $unit->warehouse_id;
        $this->cfg['posisi_input'] = $unit->posisi ?? '';
        $this->cfg['panel_tab']    = 'status';
    }

    public function simpan(): void
    {
        $this->cfg['success']     = false;
        $this->cfg['success_msg'] = '';

        if (!$this->unit || !$this->cfg['aksi']) return;

        $unit = ProductUnit::find($this->unit['id']);
        if (!$unit) return;

        $allowed = $this->getAllowedTransitions($unit->status);
        if (!in_array($this->cfg['aksi'], $allowed)) {
            session()->flash('error', "Transisi tidak valid: {$unit->status} → {$this->cfg['aksi']}");
            return;
        }

        $warehouseId = $this->cfg['warehouse_id'] ? (int) $this->cfg['warehouse_id'] : null;
        $catatan     = $this->cfg['catatan'] ?: null;

        $unit->updateStatus($this->cfg['aksi'], $warehouseId, $catatan);

        $label = ProductUnit::STATUSES[$this->cfg['aksi']] ?? $this->cfg['aksi'];
        $this->cfg['success']      = true;
        $this->cfg['success_msg']  = "Unit {$unit->unit_code} → {$label}";
        $this->cfg['scan_input']   = '';
        $this->cfg['aksi']         = '';
        $this->cfg['catatan']      = '';
        $this->cfg['warehouse_id'] = null;
        $this->unit                = null;
    }

    public function simpanPosisi(): void
    {
        if (!$this->unit) return;

        $posisi = trim($this->cfg['posisi_input'] ?: '');

        ProductUnit::where('id', $this->unit['id'])
            ->update(['posisi' => $posisi ?: null]);

        $this->cfg['success']     = true;
        $this->cfg['success_msg'] = "Posisi unit {$this->unit['unit_code']} diperbarui" . ($posisi ? " → {$posisi}" : " (dihapus)");
        $this->cfg['scan_input']  = '';
        $this->unit               = null;
    }

    public function simpanPindah(): void
    {
        if (!$this->unit) return;

        $tujuanId = $this->cfg['gudang_tujuan'] ? (int) $this->cfg['gudang_tujuan'] : null;
        if (!$tujuanId) {
            session()->flash('error', 'Pilih gudang tujuan terlebih dahulu');
            return;
        }
        if ($tujuanId === (int) $this->unit['warehouse_id']) {
            session()->flash('error', 'Gudang tujuan sama dengan gudang sekarang');
            return;
        }

        $unit = ProductUnit::find($this->unit['id']);
        if (!$unit) return;

        if (!in_array($unit->status, ['stok', 'dikembalikan'])) {
            session()->flash('error', 'Unit harus berstatus Stok atau Dikembalikan untuk dipindah');
            return;
        }

        $asalId = $unit->warehouse_id;
        $unit->update(['warehouse_id' => $tujuanId]);

        InventoryTransaction::catatPindahGudang(
            $unit->unit_code, $unit->product_id, $asalId, $tujuanId,
            $this->cfg['pindah_catatan'] ?: null
        );

        $tujuan = Warehouse::find($tujuanId);
        $this->cfg['success']        = true;
        $this->cfg['success_msg']    = "Unit {$unit->unit_code} dipindahkan ke {$tujuan->nama}";
        $this->cfg['scan_input']     = '';
        $this->cfg['gudang_tujuan']  = null;
        $this->cfg['pindah_catatan'] = '';
        $this->unit                  = null;
    }

    public function render()
    {
        return view('livewire.user-scan-barcode');
    }
}
