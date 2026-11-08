<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class AttendanceExport
{
    public function __construct(
        protected ?string $dari = null,
        protected ?string $sampai = null,
        protected ?string $type = null,
        protected ?string $mode = null,
        protected ?string $role = null,
    ) {}

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'absensi-' . now()->format('Y-m-d-His') . '.xlsx';

        return response()->streamDownload(function () {
            $writer = new Writer();
            $writer->openToFile('php://output');

            if ($this->mode === 'rekap') {
                $this->writeRekapSheet($writer);
            } else {
                $this->writeDetailSheet($writer);
                $writer->addNewSheetAndMakeItCurrent();
                $this->writeSummarySheet($writer);
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function writeRekapSheet(Writer $writer): void
    {
        $dari   = $this->dari ? Carbon::parse($this->dari) : now()->startOfMonth();
        $sampai = $this->sampai ? Carbon::parse($this->sampai) : now()->endOfMonth();
        $days   = $dari->daysInMonth;
        $bulan  = $dari->translatedFormat('F Y');

        $globalStart = \App\Models\UserWorkSchedule::globalStart();
        $customMap   = \App\Models\UserWorkSchedule::customMap();

        $bold   = (new Style())->setFontBold();
        $header = (new Style())->setFontBold()->setBackgroundColor('D9D9D9');

        // Info baris atas
        $filterRoleLabel = $this->role ? ' | Role: ' . $this->role : '';
        $writer->addRow(Row::fromValues(['Jenis absen : Masuk/Keluar' . $filterRoleLabel], $bold));
        $writer->addRow(Row::fromValues(['Bulan: ' . $bulan], $bold));
        $writer->addRow(Row::fromValues(['Ket: * = terlambat | kolom kosong = tidak hadir | - = Minggu']));
        $writer->addRow(Row::fromValues([]));

        // Header kolom tanggal
        $headerRow = ['Nama', 'Role'];
        for ($d = 1; $d <= $days; $d++) {
            $headerRow[] = (string) $d;
        }
        $headerRow[] = 'Hadir';
        $headerRow[] = 'Telat';
        $headerRow[] = 'Tidak Hadir';

        $writer->addRow(Row::fromValues($headerRow, $header));

        // Data per user
        $userIds = Attendance::whereBetween('noted_at', [$dari, $sampai])
            ->distinct()->pluck('user_id');

        $userQuery = User::whereIn('id', $userIds)->orderBy('name');
        if ($this->role) {
            $userQuery->whereHas('roles', fn($q) => $q->where('name', $this->role));
        }

        $userQuery->each(function (User $user) use ($writer, $dari, $sampai, $days, $globalStart, $customMap) {
            $batasMenitTotal = \App\Models\UserWorkSchedule::lateLimitMinutes($customMap[$user->id] ?? $globalStart);

            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('noted_at', [$dari, $sampai])
                ->get();

            $byDate = [];
            foreach ($attendances as $att) {
                $d = $att->noted_at->day;
                $byDate[$d][] = $att;
            }

            $cells = [$user->name, $user->getRoleNames()->join(', ')];

            $totalHadir = 0;
            $totalTelat = 0;
            $hariKerja  = 0;

            for ($day = 1; $day <= $days; $day++) {
                $isSun   = Carbon::create($dari->year, $dari->month, $day)->isSunday();
                $records = $byDate[$day] ?? [];
                $clockIn = collect($records)->firstWhere('type', 'clock_in');

                if ($isSun) {
                    $cells[] = '';
                } elseif ($clockIn) {
                    $hariKerja++;
                    $jamMasuk    = $clockIn->noted_at;
                    $menitMasuk  = $jamMasuk->hour * 60 + $jamMasuk->minute;
                    $isTelat     = $menitMasuk > $batasMenitTotal;
                    $totalHadir++;
                    if ($isTelat) $totalTelat++;
                    $cells[] = $jamMasuk->format('H:i') . ($isTelat ? ' *' : '');
                } else {
                    $hariKerja++;
                    $cells[] = '';
                }
            }

            $tidakHadir = $hariKerja - $totalHadir;
            $cells[]    = $totalHadir;
            $cells[]    = $totalTelat;
            $cells[]    = $tidakHadir;

            $writer->addRow(Row::fromValues($cells));
        });

        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['* Merah = terlambat | Minggu = tidak dihitung'], (new Style())->setFontColor(Color::RED)));
    }

    private function writeDetailSheet(Writer $writer): void
    {
        $bold = (new Style())->setFontBold();

        $writer->addRow(Row::fromValues([
            'Nama', 'Email', 'Tanggal',
            'Jam Masuk', 'Valid Masuk', 'Jarak Masuk (m)',
            'Jam Pulang', 'Valid Pulang', 'Jarak Pulang (m)',
        ], $bold));

        $query = Attendance::with('user')
            ->whereIn('type', ['clock_in', 'clock_out'])
            ->when($this->dari, fn($q) => $q->whereDate('noted_at', '>=', $this->dari))
            ->when($this->sampai, fn($q) => $q->whereDate('noted_at', '<=', $this->sampai))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->orderBy('noted_at', 'asc')
            ->get();

        $grouped = $query->groupBy(fn($a) => $a->user_id . '_' . $a->noted_at->toDateString());

        foreach ($grouped as $records) {
            $clockIn  = $records->firstWhere('type', 'clock_in');
            $clockOut = $records->firstWhere('type', 'clock_out');
            $user     = $clockIn?->user ?? $clockOut?->user;

            $writer->addRow(Row::fromValues([
                $user?->name ?? '-',
                $user?->email ?? '-',
                ($clockIn ?? $clockOut)?->noted_at->format('d/m/Y') ?? '-',
                $clockIn?->noted_at?->format('H:i') ?? '-',
                $clockIn ? ($clockIn->is_valid ? 'Valid' : 'Tidak Valid') : '-',
                $clockIn?->distance_from_office ?? '-',
                $clockOut?->noted_at?->format('H:i') ?? '-',
                $clockOut ? ($clockOut->is_valid ? 'Valid' : 'Tidak Valid') : '-',
                $clockOut?->distance_from_office ?? '-',
            ]));
        }
    }

    private function writeSummarySheet(Writer $writer): void
    {
        $bold = (new Style())->setFontBold();

        $writer->addRow(Row::fromValues([
            'Nama', 'Email', 'Periode',
            'Total Hadir', 'Tepat Waktu', 'Terlambat',
            'Total Izin', 'Total Cuti', 'Tidak Valid',
        ], $bold));

        $periode = ($this->dari ?? '-') . ' s/d ' . ($this->sampai ?? '-');

        $userIds = Attendance::when($this->dari, fn($q) => $q->whereDate('noted_at', '>=', $this->dari))
            ->when($this->sampai, fn($q) => $q->whereDate('noted_at', '<=', $this->sampai))
            ->distinct()->pluck('user_id');

        User::whereIn('id', $userIds)->orderBy('name')->each(function (User $user) use ($writer, $periode) {
            $attendances = Attendance::where('user_id', $user->id)
                ->when($this->dari, fn($q) => $q->whereDate('noted_at', '>=', $this->dari))
                ->when($this->sampai, fn($q) => $q->whereDate('noted_at', '<=', $this->sampai))
                ->get();

            $clockIns = $attendances->where('type', 'clock_in');

            $writer->addRow(Row::fromValues([
                $user->name,
                $user->email,
                $periode,
                $clockIns->count(),
                $clockIns->where('is_valid', true)->count(),
                $clockIns->where('is_valid', false)->count(),
                $attendances->where('type', 'permission')->count(),
                $attendances->where('type', 'leave')->count(),
                $attendances->where('is_valid', false)->count(),
            ]));
        });
    }
}
