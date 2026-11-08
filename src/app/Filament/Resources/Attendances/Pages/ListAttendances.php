<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Widgets\AttendanceRecapWidget;
use App\Models\User;
use App\Models\UserWorkSchedule;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->customAbsensiAction(),
            CreateAction::make(),
        ];
    }

    protected function customAbsensiAction(): Action
    {
        return Action::make('customAbsensi')
            ->label('Custom Absensi')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->visible(fn (): bool => auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false)
            ->modalHeading('Custom Jam Masuk per User')
            ->modalDescription(function (): string {
                $tol = UserWorkSchedule::toleranceMinutes();
                $tolText = $tol > 0 ? "toleransi terlambat {$tol} menit" : 'tanpa toleransi';

                return 'Jam masuk standar: ' . UserWorkSchedule::globalStart() . " ({$tolText}). "
                    . 'User yang tidak ada di daftar ini memakai jam standar. '
                    . 'Toleransi tetap dihitung dari jam masuk custom masing-masing user.';
            })
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel('Simpan')
            ->fillForm(fn (): array => [
                'schedules' => UserWorkSchedule::query()
                    ->orderBy('work_start_time')
                    ->get()
                    ->map(fn (UserWorkSchedule $s): array => [
                        'user_id'         => $s->user_id,
                        'work_start_time' => $s->work_start_time,
                    ])
                    ->all(),
            ])
            ->schema([
                Repeater::make('schedules')
                    ->label('Jam masuk custom')
                    ->addActionLabel('Tambah user')
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->columns(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->options(fn (): array => User::query()
                                ->where('status', 'active')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->columnSpan(2),
                        TimePicker::make('work_start_time')
                            ->label('Jam masuk')
                            ->seconds(false)
                            ->format('H:i')
                            ->required(),
                    ]),
            ])
            ->action(function (array $data): void {
                $rows = collect($data['schedules'] ?? [])
                    ->filter(fn (array $r): bool => ! empty($r['user_id']) && ! empty($r['work_start_time']))
                    ->mapWithKeys(fn (array $r): array => [(int) $r['user_id'] => substr((string) $r['work_start_time'], 0, 5)]);

                DB::transaction(function () use ($rows): void {
                    UserWorkSchedule::query()->whereNotIn('user_id', $rows->keys())->delete();
                    foreach ($rows as $userId => $time) {
                        UserWorkSchedule::updateOrCreate(['user_id' => $userId], ['work_start_time' => $time]);
                    }
                });

                Notification::make()
                    ->title('Custom jam masuk disimpan')
                    ->body($rows->count() > 0
                        ? $rows->count() . ' user memakai jam masuk custom.'
                        : 'Semua user memakai jam masuk standar.')
                    ->success()
                    ->send();
            });
    }

    protected function getFooterWidgets(): array
    {
        return [
            AttendanceRecapWidget::class,
        ];
    }
}
