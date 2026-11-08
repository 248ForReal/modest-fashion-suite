<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiOrderItem extends Model
{
    protected $fillable = ['distribusi_order_id', 'product_id', 'jumlah', 'posisi_tujuan'];

    public function order(): BelongsTo   { return $this->belongsTo(DistribusiOrder::class, 'distribusi_order_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
