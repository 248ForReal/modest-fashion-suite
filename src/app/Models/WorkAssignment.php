<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class WorkAssignment extends Model
{
    protected $fillable = [
        'type', 'garment_type_id', 'work_type_id', 'product_id', 'hasil_jumlah',
        'assigned_to', 'created_by',
        'target_jumlah', 'deadline', 'instruksi', 'foto_instruksi', 'link_instruksi',
        'status', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline'     => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function garmentType(): BelongsTo
    {
        return $this->belongsTo(GarmentType::class);
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(WorkSubmission::class);
    }

    public function latestSubmission(): HasOne
    {
        return $this->hasOne(WorkSubmission::class)->latestOfMany();
    }

    public function cancelRequests(): HasMany
    {
        return $this->hasMany(WorkCancelRequest::class);
    }

    public function latestCancelRequest(): HasOne
    {
        return $this->hasOne(WorkCancelRequest::class)->latestOfMany();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'submitted', 'revision', 'cancel_requested']);
    }

    public function isPool(): bool
    {
        return $this->type === 'pool';
    }

    public function isPersonal(): bool
    {
        return $this->type === 'personal';
    }

    // Tugas aktif milik user (personal assigned atau pool yang sudah diklaim)
    public function scopeActiveFor(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId)
            ->whereIn('status', ['open', 'in_progress', 'submitted', 'revision']);
    }

    // Tugas pool yang belum diklaim siapapun
    public function scopeOpenPool(Builder $query): Builder
    {
        return $query->where('type', 'pool')
            ->where('status', 'open')
            ->whereNull('assigned_to');
    }
}
