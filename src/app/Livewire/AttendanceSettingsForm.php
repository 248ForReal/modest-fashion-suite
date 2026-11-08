<?php

namespace App\Livewire;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendanceSettingsForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'office_latitude'        => Setting::get('office_latitude', ''),
            'office_longitude'       => Setting::get('office_longitude', ''),
            'attendance_radius'      => Setting::get('attendance_radius', '100'),
            'work_start_time'        => Setting::get('work_start_time', '08:00'),
            'work_end_time'          => Setting::get('work_end_time', '17:00'),
            'late_tolerance_minutes' => Setting::get('late_tolerance_minutes', '15'),
            'late_tolerance_active'  => Setting::get('late_tolerance_active', 'true') === 'true',
            'work_cooldown_minutes'  => Setting::get('work_cooldown_minutes', '2'),
            'allow_outside_radius'   => Setting::get('allow_outside_radius', 'false') === 'true',
            'attendance_module'      => Setting::get('attendance_module', 'true') === 'true',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Lokasi Kantor')
                    ->schema([
                        TextInput::make('office_latitude')
                            ->label('Latitude')
                            ->required()
                            ->numeric(),
                        TextInput::make('office_longitude')
                            ->label('Longitude')
                            ->required()
                            ->numeric(),
                        TextInput::make('attendance_radius')
                            ->label('Radius (meter)')
                            ->required()
                            ->numeric()
                            ->suffix('m')
                            ->minValue(10),
                    ])->columns(3),

                Fieldset::make('Jam Kerja')
                    ->schema([
                        TextInput::make('work_start_time')
                            ->label('Jam Masuk')
                            ->required(),
                        TextInput::make('work_end_time')
                            ->label('Jam Pulang')
                            ->required(),
                        TextInput::make('late_tolerance_minutes')
                            ->label('Toleransi Terlambat')
                            ->numeric()
                            ->suffix('menit')
                            ->helperText('0 = tidak ada toleransi'),
                    ])->columns(3),

                Fieldset::make('Pengaturan Tugas Kerja')
                    ->schema([
                        TextInput::make('work_cooldown_minutes')
                            ->label('Cooldown Tugas')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('menit')
                            ->helperText('Waktu tunggu setelah tugas selesai sebelum bisa ambil tugas baru. 0 = tidak ada cooldown.'),
                    ])->columns(1),

                Fieldset::make('Aturan Absensi')
                    ->schema([
                        Toggle::make('late_tolerance_active')
                            ->label('Aktifkan toleransi keterlambatan')
                            ->helperText('Jika aktif, absen masuk dalam toleransi dianggap tepat waktu'),
                        Toggle::make('allow_outside_radius')
                            ->label('Izinkan absen di luar radius')
                            ->helperText('Karyawan tetap bisa absen meski di luar area kantor, ditandai tidak valid'),
                        Toggle::make('attendance_module')
                            ->label('Aktifkan modul absensi')
                            ->helperText('Matikan untuk menonaktifkan seluruh fitur absensi'),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function saveAction(): Action
    {
        return Action::make('save')
            ->label('Simpan Setting')
            ->action(function () {
                $data = $this->form->getState();

                Setting::set('office_latitude',        $data['office_latitude']);
                Setting::set('office_longitude',       $data['office_longitude']);
                Setting::set('attendance_radius',      $data['attendance_radius']);
                Setting::set('work_start_time',        $data['work_start_time']);
                Setting::set('work_end_time',          $data['work_end_time']);
                Setting::set('late_tolerance_minutes', $data['late_tolerance_minutes']);
                Setting::set('late_tolerance_active',  $data['late_tolerance_active'] ? 'true' : 'false');
                Setting::set('work_cooldown_minutes',  $data['work_cooldown_minutes'] ?? '2');
                Setting::set('allow_outside_radius',   $data['allow_outside_radius'] ? 'true' : 'false');
                Setting::set('attendance_module',      $data['attendance_module'] ? 'true' : 'false');

                Notification::make()
                    ->title('Setting berhasil disimpan')
                    ->success()
                    ->send();
            });
    }

    public function render(): View
    {
        return view('livewire.attendance-settings-form');
    }
}
