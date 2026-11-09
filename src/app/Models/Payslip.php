<?php

namespace App\Models;

use App\Filament\Pages\WorkSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Slip gaji bulanan satu karyawan.
 *
 * Total Pendapatan = Hasil Kerja + Uang makan + Tunjangan + Bonus kinerja
 *                  + Bonus lainnya + Jajan + Lainnya (C).
 * BPJS TK ditanggung perusahaan: hanya informasi, TIDAK masuk total.
 * Take Home Pay = Total Pendapatan - Pengurangan.
 */
class Payslip extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINAL = 'final';

    /** Kolom nominal yang dijumlahkan menjadi Total Pendapatan. */
    public const INCOME_FIELDS = [
        'piece_earnings', 'meal_allowance', 'allowance', 'performance_bonus',
        'other_bonus', 'pocket_money', 'other_income',
    ];

    protected $fillable = [
        'user_id', 'period',
        'work_days', 'absent_days', 'garment_count',
        'piece_earnings', 'meal_allowance', 'allowance', 'performance_bonus',
        'employer_bpjs', 'other_bonus', 'pocket_money', 'other_income', 'deduction',
        'total_income', 'take_home_pay',
        'bank_name', 'bank_account', 'transfer_date',
        'status', 'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'finalized_at'  => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Total selalu dihitung ulang di server; angka dari form tidak dipercaya.
        static::saving(fn (self $slip) => $slip->refreshTotals());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function refreshTotals(): void
    {
        $income = 0;
        foreach (self::INCOME_FIELDS as $field) {
            $income += (int) $this->{$field};
        }
        $this->total_income  = $income;
        $this->take_home_pay = $income - (int) $this->deduction;
    }

    /** Nama bulan Indonesia, indeks 1-12. */
    public static function monthNames(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    public static function periodLabelFor(string $period): string
    {
        [$year, $month] = array_pad(explode('-', $period), 2, '1');

        return (self::monthNames()[(int) $month] ?? $period) . ' ' . $year;
    }

    public function periodLabel(): string
    {
        return self::periodLabelFor($this->period);
    }

    /** Rupiah format Indonesia; nol ditampilkan sebagai "-" seperti pada template slip. */
    public static function rupiah(int|float|null $value, bool $dashForZero = true): string
    {
        $value = (int) $value;
        if ($value === 0 && $dashForZero) {
            return '-';
        }

        return number_format($value, 0, ',', '.');
    }

    /** Boleh mengelola semua slip (admin, atau role yang diberi menu "Slip Gaji"). */
    public static function userCanManage(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        $role = $user->roles->first()?->name;

        return $role ? WorkSettings::canAccessMenu($role, 'payslips') : false;
    }

    /** Boleh melihat/mencetak slip ini: pengelola, atau pemilik jika sudah final. */
    public function visibleTo(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::userCanManage($user) || ($this->user_id === $user->id && $this->isFinal());
    }
}
