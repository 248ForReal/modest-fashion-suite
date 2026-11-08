<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'nomor', 'tipe', 'product_id', 'warehouse_id', 'warehouse_tujuan_id',
        'unit_code', 'jumlah', 'status_dari', 'status_ke', 'catatan', 'created_by',
    ];

    public const TIPE_LABELS = [
        'masuk_stok'    => 'Masuk Stok',
        'pindah_gudang' => 'Pindah Gudang',
        'distribusi'    => 'Distribusi',
        'update_status' => 'Update Status',
        'hapus_unit'    => 'Hapus Unit',
    ];

    public const TIPE_COLORS = [
        'masuk_stok'    => 'success',
        'pindah_gudang' => 'warning',
        'distribusi'    => 'info',
        'update_status' => 'gray',
        'hapus_unit'    => 'danger',
    ];

    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function warehouseTujuan(): BelongsTo { return $this->belongsTo(Warehouse::class, 'warehouse_tujuan_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    // Ambil ukuran dari unit_code (untuk transaksi per unit seperti pindah/update_status)
    public function getUkuranAttribute(): ?string
    {
        if (!$this->unit_code) return null;
        return \App\Models\ProductUnit::with('size')
            ->where('unit_code', $this->unit_code)
            ->first()?->size?->nama;
    }

    // ── Helper statis untuk catat transaksi ──────────────────────────

    public static function catat(array $data): self
    {
        $data['nomor']      ??= static::generateNomor();
        $data['created_by'] ??= auth()->id();
        return static::create($data);
    }

    public static function generateNomor(): string
    {
        $prefix = 'TXN-' . now()->format('Ymd') . '-';
        $last   = static::where('nomor', 'like', $prefix . '%')
            ->orderByDesc('id')->value('nomor');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public static function catatMasukStok(int $productId, int $warehouseId, int $jumlah, ?string $catatan = null): void
    {
        static::catat([
            'tipe'         => 'masuk_stok',
            'product_id'   => $productId,
            'warehouse_id' => $warehouseId,
            'jumlah'       => $jumlah,
            'catatan'      => $catatan,
        ]);
    }

    public static function catatPindahGudang(string $unitCode, int $productId, int $warehouseAsal, int $warehouseTujuan, ?string $catatan = null): void
    {
        static::catat([
            'tipe'                => 'pindah_gudang',
            'product_id'          => $productId,
            'warehouse_id'        => $warehouseAsal,
            'warehouse_tujuan_id' => $warehouseTujuan,
            'unit_code'           => $unitCode,
            'jumlah'              => 1,
            'catatan'             => $catatan,
        ]);
    }

    public static function catatDistribusi(int $productId, int $warehouseAsal, int $warehouseTujuan, int $jumlah, ?string $catatan = null): void
    {
        static::catat([
            'tipe'                => 'distribusi',
            'product_id'          => $productId,
            'warehouse_id'        => $warehouseAsal,
            'warehouse_tujuan_id' => $warehouseTujuan,
            'jumlah'              => $jumlah,
            'catatan'             => $catatan,
        ]);
    }

    public static function catatUpdateStatus(string $unitCode, int $productId, int $warehouseId, string $statusDari, string $statusKe, ?string $catatan = null): void
    {
        static::catat([
            'tipe'         => 'update_status',
            'product_id'   => $productId,
            'warehouse_id' => $warehouseId,
            'unit_code'    => $unitCode,
            'jumlah'       => 1,
            'status_dari'  => $statusDari,
            'status_ke'    => $statusKe,
            'catatan'      => $catatan,
        ]);
    }
}
