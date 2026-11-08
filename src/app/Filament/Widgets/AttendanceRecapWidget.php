<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class AttendanceRecapWidget extends Widget
{
    protected string $view = 'filament.widgets.attendance-recap-widget';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 10;

    public string $bulan;
    public string $tahun;

    public function mount(): void
    {
        $this->bulan = now()->format('m');
        $this->tahun = now()->format('Y');
    }

    public function getRecap(): array
    {
        $dari   = Carbon::create((int)$this->tahun, (int)$this->bulan, 1)->startOfMonth();
        $sampai = $dari->copy()->endOfMonth();

        $userIds = Attendance::whereBetween('noted_at', [$dari, $sampai])
            ->distinct()->pluck('user_id');

        $recap = [];

        User::whereIn('id', $userIds)->orderBy('name')->each(function (User $user) use ($dari, $sampai, &$recap) {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('noted_at', [$dari, $sampai])
                ->get();

            $clockIns = $attendances->where('type', 'clock_in');

            $recap[] = [
                'name'      => $user->name,
                'email'     => $user->email,
                'roles'     => $user->getRoleNames()->join(', '),
                'hadir'     => $clockIns->count(),
                'tepat'     => $clockIns->where('is_valid', true)->count(),
                'terlambat' => $clockIns->where('is_valid', false)->count(),
                'izin'      => $attendances->where('type', 'permission')->count(),
                'cuti'      => $attendances->where('type', 'leave')->count(),
            ];
        });

        return $recap;
    }

    public function getExportUrl(): string
    {
        $dari   = Carbon::create((int)$this->tahun, (int)$this->bulan, 1)->startOfMonth()->toDateString();
        $sampai = Carbon::create((int)$this->tahun, (int)$this->bulan, 1)->endOfMonth()->toDateString();
        return route('export.attendances', ['dari' => $dari, 'sampai' => $sampai]);
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
