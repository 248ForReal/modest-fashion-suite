<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkSubmission extends Model
{
    protected $fillable = [
        'work_assignment_id', 'submitted_by',
        'jumlah_selesai', 'catatan', 'foto_path', 'nilai_poin',
    ];

    protected function casts(): array
    {
        return [
            'nilai_poin' => 'decimal:2',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WorkAssignment::class, 'work_assignment_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function review(): HasOne
    {
        return $this->hasOne(WorkReview::class);
    }

    public function computePoin(): float
    {
        $assignment = $this->assignment()->with(['garmentType', 'workType'])->first();
        if (! $assignment) {
            return 0;
        }

        $nilaiDasar = match ($assignment->workType->kategori_nilai) {
            'jahit'  => (float) $assignment->garmentType->nilai_jahit,
            'desain' => (float) $assignment->garmentType->nilai_desain,
            'pola'   => (float) $assignment->garmentType->nilai_pola,
            default  => 0,
        };

        $bobot = (float) $assignment->workType->bobot_persen / 100;

        return round($nilaiDasar * $bobot * $this->jumlah_selesai, 2);
    }
}
