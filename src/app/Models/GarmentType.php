<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GarmentType extends Model
{
    protected $fillable = [
        'garment_category_id', 'kategori', 'sub_kategori', 'contoh_pakaian', 'keterangan',
        'nilai_jahit', 'nilai_desain', 'nilai_pola', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nilai_jahit'  => 'decimal:2',
            'nilai_desain' => 'decimal:2',
            'nilai_pola'   => 'decimal:2',
            'is_active'    => 'boolean',
        ];
    }

    public function garmentCategory(): BelongsTo
    {
        return $this->belongsTo(GarmentCategory::class);
    }

    public function workAssignments(): HasMany
    {
        return $this->hasMany(WorkAssignment::class);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }
}
