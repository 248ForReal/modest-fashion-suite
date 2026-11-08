<?php

namespace App\Filament\Pages;

use App\Filament\Pages\WorkSettings;
use App\Models\InventoryTransaction;
use App\Models\ProductUnit;
use App\Models\ProductUnitLog;
use App\Models\Warehouse;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ScanBarcode extends Page
{
    protected string $view = 'filament.pages.scan-barcode';
    protected static ?string $navigationLabel = 'Scan Barcode';
    protected static ?string $title = 'Scan Barcode Unit';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-camera';
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 4;

    // Semua scalar state dalam array agar tidak bentrok dengan Filament property resolver
    public array $cfg = [
        'scan_input'      => '',
        'panel_tab'       => 'status',   // 'status' | 'posisi' | 'pindah'
        'aksi'            => '',
        'posisi_input'    => '',
        'gudang_tujuan'   => null,
        'pindah_catatan'  => '',
        'warehouse_id'    => null,
        'catatan'         => '',
        'success'         => false,
        'success_msg'     => '',
    ];

    // Unit tetap array — aman dari resolver
    public ?array $unit = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->hasAnyRole(['super_admin', 'admin'])) return true;
        $role = $user->roles->first()?->name;
        if (!$role) return false;
        return WorkSettings::canAccessMenu($role, 'scan_barcode');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getWarehouseOptions(): array
    {
        return Warehouse::active()->orderBy('nama')->pluck('nama', 'id')->toArray();
    }

    public function scan(): void
    {
        $this->unit                   = null;
        $this->cfg['aksi']            = '';
        $this->cfg['posisi_input']    = '';
        $this->cfg['gudang_tujuan']   = null;
        $this->cfg['pindah_catatan']  = '';
        $this->cfg['success']         = false;
        $this->cfg['success_msg']     = '';

        $code = trim($this->cfg['scan_input'] ?? '');
        if (!$code) return;

        $unit = ProductUnit::with(['product.garmentCategory', 'warehouse', 'size'])
            ->where('unit_code', $code)
            ->first();

        if (!$unit) {
            Notification::make()->title('Unit tidak ditemukan')->body("Kode: {$code}")->danger()->send();
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
            'ukuran'       => $unit->size?->nama ?? null,
        ];

        $this->cfg['warehouse_id']   = $unit->warehouse_id;
        $this->cfg['posisi_input']   = $unit->posisi ?? '';
        $this->cfg['panel_tab']      = 'status';
    }

    public function simpan(): void
    {
        $this->cfg['success']     = false;
        $this->cfg['success_msg'] = '';

        if (!$this->unit || !$this->cfg['aksi']) return;

        $unit = ProductUnit::find($this->unit['id']);
        if (!$unit) return;

        // Validasi transisi di server — jangan percaya state dari browser
        $allowed = $this->getAllowedTransitions($unit->status);
        if (!in_array($this->cfg['aksi'], $allowed)) {
            Notification::make()
                ->title('Transisi tidak valid')
                ->body("Status '{$unit->status}' tidak bisa langsung ke '{$this->cfg['aksi']}'")
                ->danger()
                ->send();
            return;
        }

        $warehouseId = $this->cfg['warehouse_id'] ? (int) $this->cfg['warehouse_id'] : null;
        $catatan     = $this->cfg['catatan'] ?: null;

        $unit->updateStatus($this->cfg['aksi'], $warehouseId, $catatan);

        Notification::make()
            ->title('Berhasil diupdate')
            ->body("Unit {$unit->unit_code} → " . (ProductUnit::STATUSES[$this->cfg['aksi']] ?? $this->cfg['aksi']))
            ->success()
            ->send();

        $this->cfg['success']      = true;
        $this->cfg['success_msg']  = "Unit {$unit->unit_code} → " . (ProductUnit::STATUSES[$this->cfg['aksi']] ?? $this->cfg['aksi']);
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

        Notification::make()
            ->title('Posisi diperbarui')
            ->body("Unit {$this->unit['unit_code']} → " . ($posisi ?: 'dihapus'))
            ->success()
            ->send();

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
            Notification::make()->title('Pilih gudang tujuan')->warning()->send();
            return;
        }
        if ($tujuanId === (int) $this->unit['warehouse_id']) {
            Notification::make()->title('Gudang tujuan sama dengan gudang sekarang')->warning()->send();
            return;
        }

        $unit = ProductUnit::find($this->unit['id']);
        if (!$unit) return;

        if (!in_array($unit->status, ['stok', 'dikembalikan'])) {
            Notification::make()->title('Unit harus berstatus Stok atau Dikembalikan untuk dipindah')->danger()->send();
            return;
        }

        $asalId = $unit->warehouse_id;
        $unit->update(['warehouse_id' => $tujuanId]);

        InventoryTransaction::catatPindahGudang(
            $unit->unit_code, $unit->product_id, $asalId, $tujuanId,
            $this->cfg['pindah_catatan'] ?: null
        );

        $tujuan = Warehouse::find($tujuanId);
        Notification::make()
            ->title('Unit dipindahkan')
            ->body("Unit {$unit->unit_code} → {$tujuan->nama}")
            ->success()
            ->send();

        $this->cfg['success']      = true;
        $this->cfg['success_msg']  = "Unit {$unit->unit_code} dipindahkan ke {$tujuan->nama}";
        $this->cfg['scan_input']   = '';
        $this->cfg['gudang_tujuan']  = null;
        $this->cfg['pindah_catatan'] = '';
        $this->unit                = null;
    }

    private static array $transitions = [
        'stok'         => ['dipesan'],
        'dipesan'      => ['dikirim', 'stok'],
        'dikirim'      => ['sampai'],
        'sampai'       => ['selesai', 'dikembalikan'],
        'selesai'      => [],
        'dikembalikan' => ['stok'],
    ];

    public function getUnitLogs(): array
    {
        if (!$this->unit) return [];

        return ProductUnitLog::with(['user', 'warehouse'])
            ->where('product_unit_id', $this->unit['id'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($log) => [
                'dari'      => ProductUnit::STATUSES[$log->status_dari] ?? $log->status_dari ?? '-',
                'ke'        => ProductUnit::STATUSES[$log->status_ke] ?? $log->status_ke,
                'ke_raw'    => $log->status_ke,
                'gudang'    => $log->warehouse?->nama ?? '-',
                'user'      => $log->user?->name ?? 'Sistem',
                'catatan'   => $log->catatan,
                'waktu'     => $log->created_at->format('d M Y H:i'),
            ])
            ->toArray();
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
}
