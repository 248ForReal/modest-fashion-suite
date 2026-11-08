<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSize extends Model
{
    protected $fillable = ['nama', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function sizeGuides(): HasMany
    {
        return $this->hasMany(SizeGuide::class);
    }

    public static function aktif()
    {
        return static::where('aktif', true)->orderBy('urutan')->orderBy('nama');
    }
}
