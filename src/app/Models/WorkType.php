<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkType extends Model
{
    protected $fillable = ['nama', 'kategori_nilai', 'bobot_persen', 'is_active'];

    protected function casts(): array
    {
        return [
            'bobot_persen' => 'decimal:2',
            'is_active'    => 'boolean',
        ];
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
