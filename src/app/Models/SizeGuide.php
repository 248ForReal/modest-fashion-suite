<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeGuide extends Model
{
    protected $fillable = ['garment_category_id', 'product_size_id', 'dimensi'];

    protected $casts = ['dimensi' => 'array'];

    public function garmentCategory(): BelongsTo
    {
        return $this->belongsTo(GarmentCategory::class);
    }

    public function productSize(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class);
    }

    // Key dimensi yang umum dipakai per tipe pakaian
    // Admin bebas isi field apapun — ini hanya panduan label tampilan
    public static function dimensiLabels(): array
    {
        return [
            'lingkar_dada'     => 'Lingkar Dada (cm)',
            'lebar_bahu'       => 'Lebar Bahu (cm)',
            'panjang_baju'     => 'Panjang Baju (cm)',
            'lingkar_pinggang' => 'Lingkar Pinggang (cm)',
            'lingkar_pinggul'  => 'Lingkar Pinggul (cm)',
            'panjang_celana'   => 'Panjang Celana (cm)',
            'lingkar_paha'     => 'Lingkar Paha (cm)',
            'lingkar_lengan'   => 'Lingkar Lengan (cm)',
            'panjang_lengan'   => 'Panjang Lengan (cm)',
        ];
    }
}
