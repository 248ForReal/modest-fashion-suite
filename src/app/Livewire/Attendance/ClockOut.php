<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClockOut extends Component
{
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $notes = 'pulang';
    public ?string $message = null;
    public string $messageType = 'success';

    public function clockOut(): void
    {
        $this->validate(['latitude' => 'required|numeric', 'longitude' => 'required|numeric']);

        $alreadyDone = Attendance::where('user_id', Auth::id())
            ->where('type', 'clock_out')
            ->whereDate('noted_at', today())
            ->exists();

        if ($alreadyDone) {
            $this->dispatch('absen-duplicate', type: 'clock_out', label: 'pulang');
            return;
        }

        $officeLat    = (float) Setting::get('office_latitude');
        $officeLon    = (float) Setting::get('office_longitude');
        $radius       = (int) Setting::get('attendance_radius', 100);
        $allowOutside = Setting::get('allow_outside_radius', 'false') === 'true';
        $distance     = Attendance::calculateDistance($this->latitude, $this->longitude, $officeLat, $officeLon);
        $inRadius     = $distance <= $radius;

        if (!$inRadius && !$allowOutside) {
            $this->dispatch('absen-blocked', distance: $distance, radius: $radius);
            return;
        }

        Attendance::create([
            'user_id'              => Auth::id(),
            'type'                 => 'clock_out',
            'latitude'             => $this->latitude,
            'longitude'            => $this->longitude,
            'distance_from_office' => $distance,
            'is_valid'             => $inRadius,
            'noted_at'             => now(),
            'notes'                => $this->notes,
        ]);

        if ($inRadius) {
            $this->dispatch('absen-success', type: 'clock_out', distance: $distance);
        } else {
            $this->dispatch('absen-outside', type: 'clock_out', distance: $distance);
        }
    }

    public function render()
    {
        $workEnd      = \App\Models\Setting::get('work_end_time', '17:00');
        $officeLat    = (float) \App\Models\Setting::get('office_latitude');
        $officeLon    = (float) \App\Models\Setting::get('office_longitude');
        $radius       = (int) \App\Models\Setting::get('attendance_radius', 100);
        $allowOutside = \App\Models\Setting::get('allow_outside_radius', 'false') === 'true';

        return view('livewire.attendance.clock-out', [
            'workEnd'      => $workEnd,
            'officeLat'    => $officeLat,
            'officeLon'    => $officeLon,
            'radius'       => $radius,
            'allowOutside' => $allowOutside,
        ]);
    }
}
