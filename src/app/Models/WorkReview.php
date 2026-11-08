<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkReview extends Model
{
    protected $fillable = [
        'work_submission_id', 'reviewed_by',
        'status', 'skor_kualitas', 'catatan_review',
    ];

    protected function casts(): array
    {
        return [
            'skor_kualitas' => 'integer',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(WorkSubmission::class, 'work_submission_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
