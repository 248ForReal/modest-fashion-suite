<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorkSchedule extends Model
{
    protected $fillable = ['user_id', 'work_start_time'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function globalStart(): string
    {
        return (string) Setting::get('work_start_time', '08:00');
    }

    /** Peta user_id => 'HH:MM' untuk semua user yang punya jam masuk custom. */
    public static function customMap(): array
    {
        return static::query()->pluck('work_start_time', 'user_id')->all();
    }

    /** Jam masuk milik satu user: custom jika ada, selain itu jam masuk standar. */
    public static function startFor(int $userId): string
    {
        return static::query()->where('user_id', $userId)->value('work_start_time') ?? static::globalStart();
    }

    /** Toleransi terlambat dalam menit (0 jika toleransi dinonaktifkan). */
    public static function toleranceMinutes(): int
    {
        $aktif = Setting::get('late_tolerance_active', 'true') === 'true';

        return $aktif ? (int) Setting::get('late_tolerance_minutes', '15') : 0;
    }

    /** Batas terlambat (menit sejak 00:00) = jam masuk + toleransi. Masuk setelah batas ini = terlambat. */
    public static function lateLimitMinutes(string $start): int
    {
        [$h, $m] = array_map('intval', explode(':', $start));

        return $h * 60 + $m + static::toleranceMinutes();
    }
}
