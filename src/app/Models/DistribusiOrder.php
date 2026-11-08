<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistribusiOrder extends Model
{
    protected $fillable = [
        'nomor', 'warehouse_asal_id', 'warehouse_tujuan_id',
        'total_unit', 'catatan', 'foto_bukti', 'status',
        'diterima_oleh', 'diterima_at', 'catatan_penerimaan', 'created_by',
    ];

    protected $casts = ['diterima_at' => 'datetime'];

    public const STATUS_LABELS = [
        'selesai'             => 'Selesai',
        'menunggu_penerimaan' => 'Menunggu Penerimaan',
        'diterima'            => 'Diterima',
    ];

    public function warehouseAsal(): BelongsTo    { return $this->belongsTo(Warehouse::class, 'warehouse_asal_id'); }
    public function warehouseTujuan(): BelongsTo  { return $this->belongsTo(Warehouse::class, 'warehouse_tujuan_id'); }
    public function createdBy(): BelongsTo        { return $this->belongsTo(User::class, 'created_by'); }
    public function diterimaOleh(): BelongsTo     { return $this->belongsTo(User::class, 'diterima_oleh'); }
    public function items(): HasMany              { return $this->hasMany(DistribusiOrderItem::class); }

    public static function generateNomor(): string
    {
        $prefix = 'DIST-' . now()->format('Ymd') . '-';
        $last   = static::where('nomor', 'like', $prefix . '%')->orderByDesc('id')->value('nomor');
        $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
