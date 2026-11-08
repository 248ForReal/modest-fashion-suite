<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarmentCategory extends Model
{
    protected $fillable = ['nama', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function garmentTypes()
    {
        return $this->hasMany(GarmentType::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }
}
