<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Payslip;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkAssignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat aturan perhitungan slip gaji.
 *
 * Bagian otomatis (dihitung dari data sistem):
 *   work_days      hari hadir (ada absen masuk, Minggu tidak dihitung, sama dengan Rekap Absensi)
 *   absent_days    hari kerja (Senin-Sabtu) dikurangi hari hadir
 *   garment_count  jumlah baju dari tugas berstatus approved yang selesai pada bulan itu
 *   meal_allowance hari hadir x tarif uang makan (full/part time)
 *   pocket_money   hari hadir x tarif jajan (full/part time)
 *   performance_bonus  nominal bonus jika garment_count >= batas
 *   deduction      hari tidak hadir x tarif jajan
 * Bagian manual (diisi admin, tidak ditimpa saat hitung ulang):
 *   piece_earnings (Hasil Kerja), allowance, other_bonus, other_income, employer_bpjs, rekening.
 */
class PayrollCalculator
{
    public const DEFAULTS = [
        'meal_full'          => 13000,
        'meal_part'          => 13000,
        'pocket_full'        => 5000,
        'pocket_part'        => 5000,
        'bonus_min_garments' => 20,
        'bonus_amount'       => 300000,
        'bpjs'               => 16500,
    ];

    public const DEFAULT_COMPANY = 'ATELIER';

    /** Kolom yang dihitung ulang otomatis; sisanya milik admin. */
    public const AUTO_FIELDS = [
        'work_days', 'absent_days', 'garment_count',
        'meal_allowance', 'pocket_money', 'performance_bonus', 'deduction',
    ];

    public static function rate(string $key): int
    {
        return (int) Setting::get('payroll_' . $key, self::DEFAULTS[$key]);
    }

    public static function rates(): array
    {
        $out = [];
        foreach (array_keys(self::DEFAULTS) as $key) {
            $out[$key] = self::rate($key);
        }

        return $out;
    }

    public static function companyName(): string
    {
        return (string) Setting::get('payroll_company', self::DEFAULT_COMPANY) ?: self::DEFAULT_COMPANY;
    }

    /** Jumlah hari Senin-Sabtu dalam bulan tersebut. */
    public static function workingDaysIn(Carbon $month): int
    {
        $count = 0;
        for ($d = 1; $d <= $month->daysInMonth; $d++) {
            if (! Carbon::create($month->year, $month->month, $d)->isSunday()) {
                $count++;
            }
        }

        return $count;
    }

    /** Bagian otomatis untuk satu karyawan pada satu bulan. */
    public static function computeAuto(User $user, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $workDays = Attendance::where('user_id', $user->id)
            ->where('type', 'clock_in')
            ->whereBetween('noted_at', [$start, $end])
            ->pluck('noted_at')
            ->map(fn ($at) => $at->toDateString())
            ->unique()
            ->filter(fn ($date) => ! Carbon::parse($date)->isSunday())
            ->count();

        $absentDays = max(0, self::workingDaysIn($month) - $workDays);

        $garments = WorkAssignment::where('assigned_to', $user->id)
            ->where('status', 'approved')
            ->whereBetween('completed_at', [$start, $end])
            ->with('latestSubmission')
            ->get()
            ->sum(fn ($assignment) => (int) ($assignment->latestSubmission?->jumlah_selesai ?? 0));

        $type   = $user->employment_type === 'part_time' ? 'part' : 'full';
        $meal   = self::rate('meal_' . $type);
        $pocket = self::rate('pocket_' . $type);

        $minGarments = self::rate('bonus_min_garments');
        $bonus = ($minGarments > 0 && $garments >= $minGarments) ? self::rate('bonus_amount') : 0;

        return [
            'work_days'         => $workDays,
            'absent_days'       => $absentDays,
            'garment_count'     => $garments,
            'meal_allowance'    => $workDays * $meal,
            'pocket_money'      => $workDays * $pocket,
            'performance_bonus' => $bonus,
            'deduction'         => $absentDays * $pocket,
        ];
    }

    /** Nilai awal bagian manual saat slip dibuat pertama kali. */
    public static function manualDefaults(User $user): array
    {
        return [
            'piece_earnings' => 0,
            'allowance'      => (int) $user->default_allowance,
            'other_bonus'    => 0,
            'other_income'   => 0,
            'employer_bpjs'  => self::rate('bpjs'),
            'bank_name'      => $user->bank_name,
            'bank_account'   => $user->bank_account,
        ];
    }

    /** Karyawan yang layak diberi slip: aktif, bukan tamu/member; opsional hanya yang ada absensi. */
    public static function eligibleUsers(Carbon $month, bool $onlyWithAttendance = true): Collection
    {
        $query = User::query()
            ->where('status', 'active')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['guest', 'member']))
            ->orderBy('name');

        if ($onlyWithAttendance) {
            $query->whereIn('id', Attendance::whereBetween('noted_at', [
                $month->copy()->startOfMonth(), $month->copy()->endOfMonth(),
            ])->select('user_id'));
        }

        return $query->get();
    }

    /**
     * Buat slip draft untuk banyak karyawan.
     * Slip final tidak pernah disentuh. Slip draft yang sudah ada hanya dihitung ulang
     * (bagian otomatis) bila $recalcDrafts true.
     *
     * @return array{created:int, updated:int, skipped:int}
     */
    public static function generate(Carbon $month, Collection $users, bool $recalcDrafts = false): array
    {
        $period = $month->format('Y-m');
        $stats  = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($users, $month, $period, $recalcDrafts, &$stats) {
            foreach ($users as $user) {
                $slip = Payslip::where('user_id', $user->id)->where('period', $period)->first();

                if (! $slip) {
                    Payslip::create(array_merge(
                        ['user_id' => $user->id, 'period' => $period, 'status' => Payslip::STATUS_DRAFT],
                        self::manualDefaults($user),
                        self::computeAuto($user, $month),
                    ));
                    $stats['created']++;
                } elseif (! $slip->isFinal() && $recalcDrafts) {
                    $slip->update(self::computeAuto($user, $month));
                    $stats['updated']++;
                } else {
                    $stats['skipped']++;
                }
            }
        });

        return $stats;
    }
}
