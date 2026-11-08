<?php

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\Widget;

class AttendanceSettingsWidget extends Widget
{
    protected string $view = 'filament.widgets.attendance-settings-widget';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function getViewData(): array
    {
        return [
            'workStart'           => Setting::get('work_start_time', '08:00'),
            'workEnd'             => Setting::get('work_end_time', '17:00'),
            'radius'              => Setting::get('attendance_radius', 100),
            'lateTolerance'       => Setting::get('late_tolerance_minutes', 15),
            'lateToleranceActive' => Setting::get('late_tolerance_active', 'true') === 'true',
            'allowOutside'        => Setting::get('allow_outside_radius', 'false') === 'true',
            'attendanceModule'    => Setting::get('attendance_module', 'true') === 'true',
            'workCooldown'        => Setting::get('work_cooldown_minutes', 2),
        ];
    }
}
