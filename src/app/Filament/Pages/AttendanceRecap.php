<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class AttendanceRecap extends Page
{
    protected string $view = 'filament.pages.attendance-recap';
    protected static ?string $navigationLabel = 'Rekap Absensi';
    protected static ?string $title = 'Rekap Absensi';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static \UnitEnum|string|null $navigationGroup = 'Absensi';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->hasAnyRole(['super_admin', 'admin'])) return true;
        $role = $user->roles->first()?->name;
        if (!$role) return false;
        return \App\Filament\Pages\WorkSettings::canAccessMenu($role, 'attendance_recap');
    }

    public string $bulan;
    public string $tahun;
    public string $filterRole = '';

    public function mount(): void
    {
        $this->bulan = now()->format('m');
        $this->tahun = now()->format('Y');
    }

    public function getRoleOptions(): array
    {
        return \Spatie\Permission\Models\Role::orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }

    public function getDaysInMonth(): int
    {
        return Carbon::create((int)$this->tahun, (int)$this->bulan, 1)->daysInMonth;
    }

    public function isSunday(int $day): bool
    {
        return Carbon::create((int)$this->tahun, (int)$this->bulan, $day)->isSunday();
    }

    public function getRecap(): array
    {
        $dari   = Carbon::create((int)$this->tahun, (int)$this->bulan, 1)->startOfMonth();
        $sampai = $dari->copy()->endOfMonth();
        $days   = $dari->daysInMonth;

        // Jam masuk standar + jam masuk custom per user (Absensi > Custom Absensi); toleransi dihitung dari jam masuk tiap user
        $globalStart = \App\Models\UserWorkSchedule::globalStart();
        $customMap   = \App\Models\UserWorkSchedule::customMap();

        $userIds = Attendance::whereBetween('noted_at', [$dari, $sampai])
            ->distinct()->pluck('user_id');

        $recap = [];

        $query = User::whereIn('id', $userIds)->orderBy('name');
        if ($this->filterRole !== '') {
            $query->whereHas('roles', fn($q) => $q->where('name', $this->filterRole));
        }

        $query->each(function (User $user) use ($dari, $sampai, $days, $globalStart, $customMap, &$recap) {
            $jamMasukUser    = $customMap[$user->id] ?? $globalStart;
            $batasMenitTotal = \App\Models\UserWorkSchedule::lateLimitMinutes($jamMasukUser);

            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('noted_at', [$dari, $sampai])
                ->get();

            $byDate = [];
            foreach ($attendances as $att) {
                $d = $att->noted_at->day;
                if (!isset($byDate[$d])) $byDate[$d] = [];
                $byDate[$d][] = $att;
            }

            $perTanggal = [];
            $totalHadir = 0;
            $totalTelat = 0;

            for ($day = 1; $day <= $days; $day++) {
                $isSunday = Carbon::create((int)$dari->year, (int)$dari->month, $day)->isSunday();
                $records  = $byDate[$day] ?? [];
                $clockIn  = collect($records)->firstWhere('type', 'clock_in');

                if ($isSunday) {
                    $perTanggal[$day] = ['type' => 'sunday', 'jam' => null, 'telat' => false];
                } elseif ($clockIn) {
                    $jamMasuk        = $clockIn->noted_at;
                    $menitMasuk      = $jamMasuk->hour * 60 + $jamMasuk->minute;
                    $telat           = $menitMasuk > $batasMenitTotal;
                    $totalHadir++;
                    if ($telat) $totalTelat++;
                    $perTanggal[$day] = [
                        'type' => 'hadir',
                        'jam'  => $jamMasuk->format('H:i'),
                        'telat'=> $telat,
                    ];
                } else {
                    $perTanggal[$day] = ['type' => 'kosong', 'jam' => null, 'telat' => false];
                }
            }

            $recap[] = [
                'name'        => $user->name,
                'jam_masuk'   => $jamMasukUser,
                'custom'      => isset($customMap[$user->id]),
                'roles'       => $user->getRoleNames()->join(', '),
                'per_tanggal' => $perTanggal,
                'total_hadir' => $totalHadir,
                'total_telat' => $totalTelat,
                'tidak_hadir' => ($days - collect(range(1, $days))->filter(fn($d) => Carbon::create((int)$dari->year, (int)$dari->month, $d)->isSunday())->count()) - $totalHadir,
            ];
        });

        return $recap;
    }

    public function getExportUrl(): string
    {
        $dari   = Carbon::create((int)$this->tahun, (int)$this->bulan, 1)->startOfMonth()->toDateString();
        $sampai = Carbon::create((int)$this->tahun, (int)$this->bulan, 1)->endOfMonth()->toDateString();
        $params = ['dari' => $dari, 'sampai' => $sampai, 'mode' => 'rekap'];
        if ($this->filterRole !== '') {
            $params['role'] = $this->filterRole;
        }
        return route('export.attendances', $params);
    }

    public function getBulanOptions(): array
    {
        return [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',   '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',    '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober', '11' => 'November',  '12' => 'Desember',
        ];
    }

    public function getTahunOptions(): array
    {
        $tahun = now()->year;
        return array_combine(range($tahun, $tahun - 3), range($tahun, $tahun - 3));
    }
}
