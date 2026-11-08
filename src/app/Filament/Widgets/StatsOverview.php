<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();

        return [
            Stat::make('Total Karyawan', User::whereHas('roles')->count())
                ->description('Pengguna dengan role aktif')
                ->color('primary'),

            Stat::make('Absen Hari Ini', Attendance::whereDate('noted_at', $today)->where('type', 'clock_in')->count())
                ->description('Clock in hari ini')
                ->color('success'),

            Stat::make('Menunggu Persetujuan', User::where('status', 'pending')->count())
                ->description('Akun pending approval')
                ->color('warning'),

            Stat::make('Izin Bulan Ini', Attendance::whereMonth('noted_at', now()->month)->whereIn('type', ['leave', 'permission'])->count())
                ->description('Izin & cuti bulan ini')
                ->color('info'),
        ];
    }
}
