<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\UserWorkSchedule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClockIn extends Component
{
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $notes = 'masuk';
    public ?string $message = null;
    public string $messageType = 'success';

    public function clockIn(): void
    {
        $this->record('clock_in');
    }

    public function clockOut(): void
    {
        $this->record('clock_out');
    }

    private function record(string $type): void
    {
        $this->validate(['latitude' => 'required|numeric', 'longitude' => 'required|numeric']);

        $alreadyDone = Attendance::where('user_id', Auth::id())
            ->where('type', $type)
            ->whereDate('noted_at', today())
            ->exists();

        if ($alreadyDone) {
            $label = $type === 'clock_in' ? 'masuk' : 'pulang';
            $this->dispatch('absen-duplicate', type: $type, label: $label);
            return;
        }

        $officeLat           = (float) Setting::get('office_latitude');
        $officeLon           = (float) Setting::get('office_longitude');
        $radius              = (int) Setting::get('attendance_radius', 100);
        $allowOutside        = Setting::get('allow_outside_radius', 'false') === 'true';
        $lateToleranceActive = Setting::get('late_tolerance_active', 'true') === 'true';
        $lateTolerance       = (int) Setting::get('late_tolerance_minutes', 15);
        $workStart           = UserWorkSchedule::startFor((int) Auth::id());
        $workEnd             = Setting::get('work_end_time', '17:00');

        $distance = Attendance::calculateDistance($this->latitude, $this->longitude, $officeLat, $officeLon);
        $inRadius = $distance <= $radius;

        if (!$inRadius && !$allowOutside) {
            $this->dispatch('absen-blocked', distance: $distance, radius: $radius);
            return;
        }

        // Cek apakah tepat waktu (dengan toleransi)
        $isOnTime = true;
        if ($type === 'clock_in') {
            [$h, $m] = explode(':', $workStart);
            $deadline = now()->setHour((int)$h)->setMinute((int)$m + ($lateToleranceActive ? $lateTolerance : 0))->setSecond(0);
            $isOnTime = now()->lte($deadline);
        }

        Attendance::create([
            'user_id'              => Auth::id(),
            'type'                 => $type,
            'latitude'             => $this->latitude,
            'longitude'            => $this->longitude,
            'distance_from_office' => $distance,
            'is_valid'             => $inRadius && $isOnTime,
            'noted_at'             => now(),
            'notes'                => $this->notes,
        ]);

        if ($inRadius) {
            $this->dispatch('absen-success', type: $type, distance: $distance, onTime: $isOnTime);
        } else {
            $this->dispatch('absen-outside', type: $type, distance: $distance);
        }
    }

    public function render()
    {
        $workStart    = UserWorkSchedule::startFor((int) Auth::id());
        $officeLat    = (float) Setting::get('office_latitude');
        $officeLon    = (float) Setting::get('office_longitude');
        $radius       = (int) Setting::get('attendance_radius', 100);
        $allowOutside = Setting::get('allow_outside_radius', 'false') === 'true';

        return view('livewire.attendance.clock-in', [
            'workStart'    => $workStart,
            'officeLat'    => $officeLat,
            'officeLon'    => $officeLon,
            'radius'       => $radius,
            'allowOutside' => $allowOutside,
        ]);
    }
}
