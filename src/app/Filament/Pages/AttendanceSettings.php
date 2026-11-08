<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Request;

class AttendanceSettings extends Page
{
    protected string $view = 'filament.pages.attendance-settings';
    protected static ?string $navigationLabel = 'Setting Absensi';
    protected static ?string $title = 'Setting Absensi';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static \UnitEnum|string|null $navigationGroup = 'Absensi';
    protected static ?int $navigationSort = 3;

    public string $office_latitude        = '';
    public string $office_longitude       = '';
    public string $attendance_radius      = '';
    public string $work_start_time        = '';
    public string $work_end_time          = '';
    public string $late_tolerance_minutes  = '';
    public string $work_cooldown_minutes   = '2';
    public bool   $allow_outside_radius   = false;
    public bool   $attendance_module      = true;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function mount(): void
    {
        $this->office_latitude        = Setting::get('office_latitude', '');
        $this->office_longitude       = Setting::get('office_longitude', '');
        $this->attendance_radius      = Setting::get('attendance_radius', '100');
        $this->work_start_time        = Setting::get('work_start_time', '08:00');
        $this->work_end_time          = Setting::get('work_end_time', '17:00');
        $this->late_tolerance_minutes  = Setting::get('late_tolerance_minutes', '15');
        $this->work_cooldown_minutes   = Setting::get('work_cooldown_minutes', '2');
        $this->allow_outside_radius   = Setting::get('allow_outside_radius', 'false') === 'true';
        $this->attendance_module      = Setting::get('attendance_module', 'true') === 'true';
    }

    public function save(): void
    {
        $this->validate([
            'office_latitude'        => 'required|numeric',
            'office_longitude'       => 'required|numeric',
            'attendance_radius'      => 'required|integer|min:10',
            'work_start_time'        => 'required',
            'work_end_time'          => 'required',
            'late_tolerance_minutes' => 'required|integer|min:0',
            'work_cooldown_minutes'  => 'required|integer|min:0',
        ]);

        Setting::set('office_latitude',        $this->office_latitude);
        Setting::set('office_longitude',       $this->office_longitude);
        Setting::set('attendance_radius',      $this->attendance_radius);
        Setting::set('work_start_time',        $this->work_start_time);
        Setting::set('work_end_time',          $this->work_end_time);
        Setting::set('late_tolerance_minutes', $this->late_tolerance_minutes);
        Setting::set('work_cooldown_minutes',  $this->work_cooldown_minutes);
        Setting::set('allow_outside_radius',   $this->allow_outside_radius ? 'true' : 'false');
        Setting::set('attendance_module',      $this->attendance_module ? 'true' : 'false');

        Notification::make()
            ->title('Setting berhasil disimpan')
            ->success()
            ->send();
    }
}
