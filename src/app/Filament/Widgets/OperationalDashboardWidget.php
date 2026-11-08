<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\ProductUnit;
use App\Models\User;
use App\Models\WorkAssignment;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class OperationalDashboardWidget extends Widget
{
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;
    protected string $view = 'filament.widgets.operational-dashboard';

    protected static bool $canView = true;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function getData(): array
    {
        $today = Carbon::today();

        // Unit dalam perjalanan (dikirim)
        $unitDikirim = ProductUnit::with(['product', 'warehouse'])
            ->where('status', 'dikirim')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($u) => [
                'code'    => $u->unit_code,
                'produk'  => $u->product?->nama ?? '-',
                'gudang'  => $u->warehouse?->nama ?? '-',
                'sejak'   => $u->updated_at->diffForHumans(),
            ])
            ->toArray();

        $totalDikirim = ProductUnit::where('status', 'dikirim')->count();

        // Unit sampai tapi belum diselesaikan
        $unitSampai = ProductUnit::where('status', 'sampai')->count();

        // Tugas deadline minggu ini
        $tugasDeadline = WorkAssignment::with(['assignedTo', 'garmentType'])
            ->whereIn('status', ['open', 'in_progress'])
            ->whereBetween('deadline', [$today, $today->copy()->addDays(7)])
            ->orderBy('deadline')
            ->limit(5)
            ->get()
            ->map(fn ($w) => [
                'id'       => $w->id,
                'nama'     => $w->garmentType?->contoh_pakaian ?? 'Tugas #' . $w->id,
                'assigned' => $w->assignedTo?->name ?? 'Pool',
                'deadline' => $w->deadline?->format('d M'),
                'sisa'     => $w->deadline?->diffInDays($today) . ' hari',
                'status'   => $w->status,
                'urgent'   => $w->deadline?->diffInDays($today) <= 2,
            ])
            ->toArray();

        $totalDeadlineMingguIni = WorkAssignment::whereIn('status', ['open', 'in_progress'])
            ->whereBetween('deadline', [$today, $today->copy()->addDays(7)])
            ->count();

        // Absensi hari ini
        $totalKaryawan   = User::whereHas('roles')->where('status', 'active')->count();
        $sudahAbsen      = Attendance::whereDate('noted_at', $today)->where('type', 'clock_in')->count();
        $belumAbsen      = max(0, $totalKaryawan - $sudahAbsen);

        // Karyawan belum absen (sample 5)
        $idSudahAbsen = Attendance::whereDate('noted_at', $today)
            ->where('type', 'clock_in')
            ->pluck('user_id');

        $belumAbsenList = User::whereHas('roles')
            ->where('status', 'active')
            ->whereNotIn('id', $idSudahAbsen)
            ->limit(5)
            ->get()
            ->map(fn ($u) => [
                'name' => $u->name,
                'role' => ucfirst(str_replace('_', ' ', $u->roles->first()?->name ?? '-')),
            ])
            ->toArray();

        // Selesai hari ini
        $selesaiHariIni = ProductUnit::where('status', 'selesai')
            ->whereDate('updated_at', $today)
            ->count();

        // Dikembalikan hari ini
        $dikembalikanHariIni = ProductUnit::where('status', 'dikembalikan')
            ->whereDate('updated_at', $today)
            ->count();

        return compact(
            'unitDikirim', 'totalDikirim',
            'unitSampai',
            'tugasDeadline', 'totalDeadlineMingguIni',
            'totalKaryawan', 'sudahAbsen', 'belumAbsen', 'belumAbsenList',
            'selesaiHariIni', 'dikembalikanHariIni'
        );
    }
}
