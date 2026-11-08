<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\InventoryTransaction;
use App\Models\ProductUnit;
use App\Models\Warehouse;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;
    protected string $view = 'filament.resources.warehouses.pages.view-warehouse';

    public array $cfg = [
        'pindah_product_id'  => null,
        'selected_units'     => [],
        'gudang_tujuan_id'   => null,
        // update status (single & bulk)
        'status_unit_code'   => null,   // single unit yang diklik
        'bulk_selected'      => [],     // unit_code[] untuk bulk update
        'bulk_mode'          => false,  // false=single, true=bulk
        'status_baru'        => null,
        'status_catatan'     => '',
        // edit posisi (single)
        'posisi_unit_code'   => null,
        'posisi_input'       => '',
        // edit posisi massal
        'posisi_bulk_mode'   => false,
        'posisi_bulk_sel'    => [],
        'posisi_bulk_input'  => '',
        // panel bawah tab aktif
        'panel_tab'          => 'status', // 'status' | 'posisi' | 'posisi_bulk'
        // UI state
        'active_tab'         => 'produk',
        'filter_status'      => '',
        'search'             => '',
        'expanded_products'  => [],
    ];

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Edit Gudang'),
        ];
    }

    public function getWarehouseOptions(): array
    {
        return Warehouse::active()
            ->where('id', '!=', $this->record->id)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    public function bukaPanel(int $productId): void
    {
        if ($this->cfg['pindah_product_id'] === $productId) {
            // Toggle tutup
            $this->cfg['pindah_product_id'] = null;
            $this->cfg['selected_units']    = [];
            $this->cfg['gudang_tujuan_id']  = null;
        } else {
            $this->cfg['pindah_product_id'] = $productId;
            $this->cfg['selected_units']    = [];
            $this->cfg['gudang_tujuan_id']  = null;
        }
    }

    public function toggleUnit(string $unitCode): void
    {
        $selected = $this->cfg['selected_units'];
        if (in_array($unitCode, $selected)) {
            $this->cfg['selected_units'] = array_values(array_filter($selected, fn($c) => $c !== $unitCode));
        } else {
            $this->cfg['selected_units'] = array_merge($selected, [$unitCode]);
        }
    }

    public function pilihSemua(int $productId): void
    {
        $units = ProductUnit::where('product_id', $productId)
            ->where('warehouse_id', $this->record->id)
            ->whereIn('status', ['stok', 'dikembalikan'])
            ->pluck('unit_code')
            ->toArray();
        $this->cfg['selected_units'] = $units;
    }

    public function batalSemua(): void
    {
        $this->cfg['selected_units'] = [];
    }

    public function toggleExpand(int $productId): void
    {
        $expanded = $this->cfg['expanded_products'];
        if (in_array($productId, $expanded)) {
            $this->cfg['expanded_products'] = array_values(array_filter($expanded, fn($id) => $id !== $productId));
        } else {
            $this->cfg['expanded_products'] = array_merge($expanded, [$productId]);
        }
    }

    public function expandAll(): void
    {
        $this->cfg['expanded_products'] = array_column($this->getUnitsByProduct(), 'product_id');
    }

    public function collapseAll(): void
    {
        $this->cfg['expanded_products'] = [];
    }

    public function pindahkan(): void
    {
        $selected   = $this->cfg['selected_units'];
        $tujuanId   = $this->cfg['gudang_tujuan_id'] ? (int) $this->cfg['gudang_tujuan_id'] : null;
        $productId  = $this->cfg['pindah_product_id'];

        if (empty($selected)) {
            Notification::make()->title('Pilih minimal 1 unit')->warning()->send();
            return;
        }
        if (!$tujuanId) {
            Notification::make()->title('Pilih gudang tujuan')->warning()->send();
            return;
        }

        $tujuan = Warehouse::find($tujuanId);
        $units  = ProductUnit::whereIn('unit_code', $selected)
            ->where('warehouse_id', $this->record->id)
            ->whereIn('status', ['stok', 'dikembalikan'])
            ->get();

        foreach ($units as $unit) {
            $unit->update(['warehouse_id' => $tujuanId]);
            InventoryTransaction::catatPindahGudang(
                $unit->unit_code, $unit->product_id, $this->record->id, $tujuanId
            );
        }

        Notification::make()
            ->title("{$units->count()} unit berhasil dipindahkan ke {$tujuan->nama}")
            ->success()
            ->send();

        $this->cfg['pindah_product_id'] = null;
        $this->cfg['selected_units']    = [];
        $this->cfg['gudang_tujuan_id']  = null;
    }

    private static array $transitions = [
        'stok'         => ['dipesan'],
        'dipesan'      => ['dikirim', 'stok'],
        'dikirim'      => ['sampai'],
        'sampai'       => ['selesai', 'dikembalikan'],
        'selesai'      => [],
        'dikembalikan' => ['stok'],
    ];

    public function bukaStatusPanel(string $unitCode): void
    {
        if ($this->cfg['status_unit_code'] === $unitCode) {
            $this->cfg['status_unit_code'] = null;
            $this->cfg['status_baru']      = null;
            $this->cfg['status_catatan']   = '';
        } else {
            // Tutup mode pindah & posisi dulu
            $this->cfg['pindah_product_id'] = null;
            $this->cfg['selected_units']    = [];
            $this->cfg['posisi_unit_code']  = null;
            $this->cfg['bulk_mode']         = false;
            $this->cfg['bulk_selected']     = [];
            $this->cfg['status_unit_code']  = $unitCode;
            $this->cfg['status_baru']       = null;
            $this->cfg['status_catatan']    = '';
            $this->cfg['panel_tab']         = 'status';
        }
    }

    public function toggleBulkUnit(string $unitCode): void
    {
        $selected = $this->cfg['bulk_selected'];
        if (in_array($unitCode, $selected)) {
            $this->cfg['bulk_selected'] = array_values(array_filter($selected, fn($c) => $c !== $unitCode));
        } else {
            $this->cfg['bulk_selected'] = array_merge($selected, [$unitCode]);
        }
    }

    public function aktifkanBulkMode(int $productId): void
    {
        $this->cfg['bulk_mode']         = true;
        $this->cfg['bulk_selected']     = [];
        $this->cfg['status_unit_code']  = null;
        $this->cfg['pindah_product_id'] = null;
        $this->cfg['status_baru']       = null;
        $this->cfg['status_catatan']    = '';
        $this->cfg['panel_tab']         = 'status';
        // Auto-expand produk itu
        if (!in_array($productId, $this->cfg['expanded_products'])) {
            $this->cfg['expanded_products'] = array_merge($this->cfg['expanded_products'], [$productId]);
        }
    }

    public function pilihSemuaBulk(int $productId, string $statusFilter = 'stok'): void
    {
        $units = ProductUnit::where('product_id', $productId)
            ->where('warehouse_id', $this->record->id)
            ->where('status', $statusFilter)
            ->pluck('unit_code')
            ->toArray();
        $this->cfg['bulk_selected'] = $units;
    }

    public function batalBulk(): void
    {
        $this->cfg['bulk_mode']        = false;
        $this->cfg['bulk_selected']    = [];
        $this->cfg['status_unit_code'] = null;
        $this->cfg['status_baru']      = null;
        $this->cfg['status_catatan']   = '';
    }

    public function updateStatusBulk(): void
    {
        $selected   = $this->cfg['bulk_selected'];
        $statusBaru = $this->cfg['status_baru'];

        if (empty($selected)) {
            Notification::make()->title('Pilih minimal 1 unit')->warning()->send();
            return;
        }
        if (!$statusBaru) {
            Notification::make()->title('Pilih status tujuan')->warning()->send();
            return;
        }

        $units   = ProductUnit::whereIn('unit_code', $selected)->where('warehouse_id', $this->record->id)->get();
        $updated = 0;
        foreach ($units as $unit) {
            $allowed = self::$transitions[$unit->status] ?? [];
            if (in_array($statusBaru, $allowed)) {
                $unit->updateStatus($statusBaru, $unit->warehouse_id, $this->cfg['status_catatan'] ?: null);
                $updated++;
            }
        }

        Notification::make()
            ->title("{$updated} unit diubah ke status " . (ProductUnit::STATUSES[$statusBaru] ?? $statusBaru))
            ->success()
            ->send();

        $this->cfg['bulk_mode']        = false;
        $this->cfg['bulk_selected']    = [];
        $this->cfg['status_baru']      = null;
        $this->cfg['status_catatan']   = '';
    }

    public function bukaPosisiPanel(string $unitCode, string $posisiSaat): void
    {
        if ($this->cfg['posisi_unit_code'] === $unitCode) {
            $this->cfg['posisi_unit_code'] = null;
            $this->cfg['posisi_input']     = '';
        } else {
            $this->cfg['status_unit_code']  = null;
            $this->cfg['pindah_product_id'] = null;
            $this->cfg['posisi_unit_code']  = $unitCode;
            $this->cfg['posisi_input']      = $posisiSaat;
            $this->cfg['panel_tab']         = 'posisi';
        }
    }

    public function simpanPosisiUnit(): void
    {
        $unitCode = $this->cfg['posisi_unit_code'];
        if (!$unitCode) return;

        ProductUnit::where('unit_code', $unitCode)
            ->where('warehouse_id', $this->record->id)
            ->update(['posisi' => $this->cfg['posisi_input'] ?: null]);

        Notification::make()->title('Posisi unit diperbarui')->success()->send();
        $this->cfg['posisi_unit_code'] = null;
        $this->cfg['posisi_input']     = '';
    }

    public function aktifkanPosisiBulkMode(): void
    {
        $this->cfg['posisi_bulk_mode']  = true;
        $this->cfg['posisi_bulk_sel']   = [];
        $this->cfg['posisi_bulk_input'] = '';
        $this->cfg['panel_tab']         = 'posisi_bulk';
        // Tutup mode lain
        $this->cfg['status_unit_code']  = null;
        $this->cfg['bulk_mode']         = false;
        $this->cfg['pindah_product_id'] = null;
        $this->cfg['posisi_unit_code']  = null;
    }

    public function togglePosisiBulk(string $unitCode): void
    {
        $sel = $this->cfg['posisi_bulk_sel'];
        if (in_array($unitCode, $sel)) {
            $this->cfg['posisi_bulk_sel'] = array_values(array_filter($sel, fn($c) => $c !== $unitCode));
        } else {
            $this->cfg['posisi_bulk_sel'] = array_merge($sel, [$unitCode]);
        }
    }

    public function pilihSemuaPosisiBulk(int $productId): void
    {
        $this->cfg['posisi_bulk_sel'] = ProductUnit::where('product_id', $productId)
            ->where('warehouse_id', $this->record->id)
            ->pluck('unit_code')
            ->toArray();
    }

    public function batalPosisiBulk(): void
    {
        $this->cfg['posisi_bulk_mode']  = false;
        $this->cfg['posisi_bulk_sel']   = [];
        $this->cfg['posisi_bulk_input'] = '';
        $this->cfg['panel_tab']         = 'status';
    }

    public function simpanPosisiBulk(): void
    {
        $sel   = $this->cfg['posisi_bulk_sel'];
        $posisi = $this->cfg['posisi_bulk_input'] ?: null;

        if (empty($sel)) {
            Notification::make()->title('Pilih minimal 1 unit')->warning()->send();
            return;
        }

        $updated = ProductUnit::whereIn('unit_code', $sel)
            ->where('warehouse_id', $this->record->id)
            ->update(['posisi' => $posisi]);

        Notification::make()
            ->title("{$updated} unit diperbarui posisinya" . ($posisi ? " → {$posisi}" : " (dihapus)"))
            ->success()
            ->send();

        $this->cfg['posisi_bulk_mode']  = false;
        $this->cfg['posisi_bulk_sel']   = [];
        $this->cfg['posisi_bulk_input'] = '';
        $this->cfg['panel_tab']         = 'status';
    }

    public function getAllowedTransitions(string $currentStatus): array
    {
        return self::$transitions[$currentStatus] ?? [];
    }

    public function updateStatusUnit(): void
    {
        $unitCode  = $this->cfg['status_unit_code'];
        $statusBaru = $this->cfg['status_baru'];

        if (!$unitCode || !$statusBaru) {
            Notification::make()->title('Pilih status baru')->warning()->send();
            return;
        }

        $unit = ProductUnit::where('unit_code', $unitCode)
            ->where('warehouse_id', $this->record->id)
            ->first();

        if (!$unit) {
            Notification::make()->title('Unit tidak ditemukan')->danger()->send();
            return;
        }

        $allowed = self::$transitions[$unit->status] ?? [];
        if (!in_array($statusBaru, $allowed)) {
            Notification::make()->title('Transisi status tidak diizinkan')->danger()->send();
            return;
        }

        $unit->updateStatus($statusBaru, $unit->warehouse_id, $this->cfg['status_catatan'] ?: null);

        Notification::make()
            ->title("Status {$unitCode} diubah ke " . ProductUnit::STATUSES[$statusBaru])
            ->success()
            ->send();

        $this->cfg['status_unit_code'] = null;
        $this->cfg['status_baru']      = null;
        $this->cfg['status_catatan']   = '';
    }

    // Semua unit di gudang ini, dikelompokkan per produk
    public function getUnitsByProduct(): array
    {
        $units = ProductUnit::with(['product.garmentCategory', 'size'])
            ->where('warehouse_id', $this->record->id)
            ->orderBy('status')
            ->orderBy('unit_code')
            ->get();

        $grouped = [];
        foreach ($units as $unit) {
            $pid = $unit->product_id;
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'product_id'   => $pid,
                    'product_code' => $unit->product->product_code,
                    'product_nama' => $unit->product->nama,
                    'kategori'     => $unit->product->garmentCategory?->nama ?? '-',
                    'harga'        => 'Rp ' . number_format($unit->product->harga, 0, ',', '.'),
                    'units'        => [],
                ];
            }
            $grouped[$pid]['units'][] = [
                'unit_code'         => $unit->unit_code,
                'status'            => $unit->status,
                'status_label'      => ProductUnit::STATUSES[$unit->status] ?? $unit->status,
                'status_color'      => ProductUnit::STATUS_COLORS[$unit->status] ?? 'gray',
                'bisa_pindah'       => in_array($unit->status, ['stok', 'dikembalikan']),
                'allowed_next'      => self::$transitions[$unit->status] ?? [],
                'posisi'            => $unit->posisi ?? null,
                'ukuran'            => $unit->size?->nama ?? null,
                'updated_at'        => $unit->updated_at->format('d M Y H:i'),
            ];
        }

        return array_values($grouped);
    }

    public function getUnitSummary(): array
    {
        $units = ProductUnit::where('warehouse_id', $this->record->id)->get();
        $summary = [];
        foreach (ProductUnit::STATUSES as $key => $label) {
            $count = $units->where('status', $key)->count();
            if ($count > 0) {
                $summary[] = [
                    'status'       => $key,
                    'status_label' => $label,
                    'status_color' => ProductUnit::STATUS_COLORS[$key] ?? 'gray',
                    'count'        => $count,
                ];
            }
        }
        return $summary;
    }
}
