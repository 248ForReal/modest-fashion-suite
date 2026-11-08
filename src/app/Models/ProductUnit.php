<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id', 'unit_code', 'warehouse_id', 'product_size_id', 'posisi', 'status',
    ];

    public const STATUSES = [
        'stok'          => 'Stok',
        'dipesan'       => 'Dipesan',
        'dikirim'       => 'Dikirim',
        'sampai'        => 'Sampai',
        'selesai'       => 'Selesai',
        'dikembalikan'  => 'Dikembalikan',
    ];

    public const STATUS_COLORS = [
        'stok'          => 'success',
        'dipesan'       => 'warning',
        'dikirim'       => 'info',
        'sampai'        => 'primary',
        'selesai'       => 'gray',
        'dikembalikan'  => 'danger',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProductUnitLog::class)->latest();
    }

    public function updateStatus(string $statusBaru, ?int $warehouseId = null, ?string $catatan = null): void
    {
        $statusLama  = $this->status;
        $resolvedWh  = $warehouseId ?? $this->warehouse_id;

        $this->update([
            'status'       => $statusBaru,
            'warehouse_id' => $resolvedWh,
        ]);

        ProductUnitLog::create([
            'product_unit_id' => $this->id,
            'user_id'         => auth()->id(),
            'status_dari'     => $statusLama,
            'status_ke'       => $statusBaru,
            'warehouse_id'    => $resolvedWh,
            'catatan'         => $catatan,
        ]);

        \App\Models\InventoryTransaction::catatUpdateStatus(
            $this->unit_code, $this->product_id, $resolvedWh, $statusLama, $statusBaru, $catatan
        );
    }

    public static function generateUnitCode(string $productCode, int $urutan): string
    {
        return $productCode . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }
}
