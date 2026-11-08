<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'product_code', 'garment_category_id', 'garment_type_id', 'warehouse_id',
        'nama', 'deskripsi', 'jumlah', 'harga', 'lokasi', 'video_url', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'harga'  => 'decimal:2',
            'jumlah' => 'integer',
        ];
    }

    public function garmentCategory(): BelongsTo
    {
        return $this->belongsTo(GarmentCategory::class);
    }

    public function garmentType(): BelongsTo
    {
        return $this->belongsTo(GarmentType::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class)->orderBy('urutan');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->orderBy('urutan');
    }

    /**
     * @param int $targetJumlah  Total unit yang diinginkan (tanpa ukuran)
     * @param int|null $warehouseId
     * @param array $sizeDistribution  [['product_size_id' => X, 'jumlah' => N], ...] — jika diisi, pakai ini
     */
    public function syncUnits(int $targetJumlah, ?int $warehouseId = null, array $sizeDistribution = []): void
    {
        $resolvedWarehouse = $warehouseId ?? $this->warehouse_id;

        if (!empty($sizeDistribution)) {
            // Mode ukuran: buat unit sesuai distribusi per size
            $maxNo     = $this->units()->max(DB::raw("CAST(SUBSTRING_INDEX(unit_code, '-', -1) AS UNSIGNED)")) ?? 0;
            $totalBaru = 0;

            foreach ($sizeDistribution as $row) {
                $sizeId = $row['product_size_id'] ?? null;
                $qty    = (int) ($row['jumlah'] ?? 0);
                if ($qty <= 0) continue;

                for ($i = 1; $i <= $qty; $i++) {
                    $maxNo++;
                    ProductUnit::create([
                        'product_id'      => $this->id,
                        'unit_code'       => ProductUnit::generateUnitCode($this->product_code, $maxNo),
                        'warehouse_id'    => $resolvedWarehouse,
                        'product_size_id' => $sizeId ?: null,
                        'status'          => 'stok',
                    ]);
                    $totalBaru++;
                }
            }

            if ($totalBaru > 0 && $resolvedWarehouse) {
                \App\Models\InventoryTransaction::catatMasukStok(
                    $this->id, $resolvedWarehouse, $totalBaru
                );
            }
        } else {
            // Mode lama: tambah unit tanpa ukuran
            $existing = $this->units()->count();

            if ($targetJumlah > $existing) {
                $toCreate = $targetJumlah - $existing;
                $maxNo    = $this->units()->max(DB::raw("CAST(SUBSTRING_INDEX(unit_code, '-', -1) AS UNSIGNED)")) ?? 0;

                for ($i = 1; $i <= $toCreate; $i++) {
                    $urutan = $maxNo + $i;
                    ProductUnit::create([
                        'product_id'   => $this->id,
                        'unit_code'    => ProductUnit::generateUnitCode($this->product_code, $urutan),
                        'warehouse_id' => $resolvedWarehouse,
                        'status'       => 'stok',
                    ]);
                }

                if ($resolvedWarehouse) {
                    \App\Models\InventoryTransaction::catatMasukStok(
                        $this->id, $resolvedWarehouse, $toCreate
                    );
                }
            }
        }

        // Sinkronkan warehouse untuk unit yang masih stok (belum dalam proses pengiriman)
        if ($resolvedWarehouse) {
            $this->units()
                ->whereIn('status', ['stok', 'dikembalikan'])
                ->whereNull('warehouse_id')
                ->update(['warehouse_id' => $resolvedWarehouse]);
        }

        // Sinkron jumlah di tabel products agar konsisten dengan jumlah unit terdaftar
        $this->update(['jumlah' => $this->units()->count()]);
    }

    public function getStokCount(): int
    {
        return $this->units()->where('status', 'stok')->count();
    }

    // Jumlah unit yang masih di stok (belum dipesan/dikirim)
    public function getStokAktifAttribute(): int
    {
        return $this->units()->where('status', 'stok')->count();
    }

    // Jumlah unit sedang dalam proses (dipesan/dikirim/sampai)
    public function getUnitAktifAttribute(): int
    {
        return $this->units()->whereIn('status', ['dipesan', 'dikirim', 'sampai'])->count();
    }

    public function hasActiveUnits(): bool
    {
        return $this->units()->whereIn('status', ['dipesan', 'dikirim', 'sampai'])->exists();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateCode(int $categoryId): string
    {
        $category = GarmentCategory::find($categoryId);
        $date     = now()->format('Ymd');
        $kode     = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category?->nama ?? 'PRD'), 0, 3));
        $random   = strtoupper(Str::random(4));

        return "{$date}{$kode}{$random}";
    }
}
