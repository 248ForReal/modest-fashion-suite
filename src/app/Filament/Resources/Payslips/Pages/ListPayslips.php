<?php

namespace App\Filament\Resources\Payslips\Pages;

use App\Filament\Resources\Payslips\PayslipResource;
use App\Models\Payslip;
use App\Models\Setting;
use App\Models\User;
use App\Services\PayrollCalculator;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ListPayslips extends ListRecords
{
    protected static string $resource = PayslipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->ratesAction(),
            $this->generateAction(),
        ];
    }

    private function generateAction(): Action
    {
        $thisYear = (int) now()->year;

        return Action::make('buatSlip')
            ->label('Buat Slip Periode')
            ->icon(Heroicon::OutlinedPlus)
            ->modalHeading('Buat Slip Gaji Periode')
            ->modalDescription('Slip dibuat sebagai draft dengan angka otomatis dari Absensi dan Tugas. Hasil Kerja diisi manual setelahnya.')
            ->modalSubmitActionLabel('Buat Slip')
            ->modalWidth(Width::TwoExtraLarge)
            ->schema([
                Select::make('month')->label('Bulan')
                    ->options(Payslip::monthNames())
                    ->default((int) now()->month)
                    ->required(),
                Select::make('year')->label('Tahun')
                    ->options(array_combine(range($thisYear - 1, $thisYear + 1), range($thisYear - 1, $thisYear + 1)))
                    ->default($thisYear)
                    ->required(),
                Toggle::make('only_attendance')
                    ->label('Hanya karyawan yang punya absensi di bulan itu')
                    ->default(true),
                Select::make('users')
                    ->label('Karyawan tertentu (kosongkan = semua yang layak)')
                    ->multiple()
                    ->searchable()
                    ->options(fn (): array => User::where('status', 'active')->orderBy('name')->pluck('name', 'id')->all()),
                Toggle::make('recalc')
                    ->label('Hitung ulang slip draft yang sudah ada')
                    ->helperText('Hanya bagian otomatis yang dihitung ulang. Angka manual dan slip final tidak berubah.')
                    ->default(false),
            ])
            ->action(function (array $data): void {
                $month = Carbon::create((int) $data['year'], (int) $data['month'], 1);

                $users = ! empty($data['users'])
                    ? User::whereIn('id', $data['users'])->where('status', 'active')->orderBy('name')->get()
                    : PayrollCalculator::eligibleUsers($month, (bool) $data['only_attendance']);

                if ($users->isEmpty()) {
                    Notification::make()->title('Tidak ada karyawan yang layak')
                        ->body('Tidak ada karyawan aktif dengan absensi pada ' . Payslip::periodLabelFor($month->format('Y-m')) . '.')
                        ->warning()->send();

                    return;
                }

                $stats = PayrollCalculator::generate($month, $users, (bool) $data['recalc']);

                Notification::make()
                    ->title('Slip ' . Payslip::periodLabelFor($month->format('Y-m')) . ' diproses')
                    ->body("Dibuat: {$stats['created']}, dihitung ulang: {$stats['updated']}, dilewati: {$stats['skipped']}.")
                    ->success()->send();
            });
    }

    private function ratesAction(): Action
    {
        return Action::make('tarif')
            ->label('Tarif & Aturan')
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->color('gray')
            ->modalHeading('Tarif & Aturan Gaji')
            ->modalDescription('Dipakai saat slip dibuat atau dihitung ulang. Slip yang sudah ada tidak berubah sendiri.')
            ->modalWidth(Width::TwoExtraLarge)
            ->fillForm(fn (): array => array_merge(
                PayrollCalculator::rates(),
                ['company' => PayrollCalculator::companyName()],
            ))
            ->schema([
                TextInput::make('company')->label('Nama perusahaan di slip')->required()->maxLength(60),
                TextInput::make('meal_full')->label('Uang makan per hari — Full time')->numeric()->minValue(0)->prefix('Rp')->required(),
                TextInput::make('meal_part')->label('Uang makan per hari — Part time')->numeric()->minValue(0)->prefix('Rp')->required(),
                TextInput::make('pocket_full')->label('Jajan per hari — Full time')->numeric()->minValue(0)->prefix('Rp')->required(),
                TextInput::make('pocket_part')->label('Jajan per hari — Part time')->numeric()->minValue(0)->prefix('Rp')->required(),
                TextInput::make('bonus_min_garments')->label('Bonus kinerja: minimal jumlah baju')->numeric()->integer()->minValue(0)
                    ->helperText('0 = bonus kinerja dimatikan.')->required(),
                TextInput::make('bonus_amount')->label('Bonus kinerja: nominal')->numeric()->minValue(0)->prefix('Rp')->required(),
                TextInput::make('bpjs')->label('BPJS TK ditanggung perusahaan (default per slip)')->numeric()->minValue(0)->prefix('Rp')->required(),
            ])
            ->action(function (array $data): void {
                Setting::set('payroll_company', trim((string) $data['company']));
                foreach (array_keys(PayrollCalculator::DEFAULTS) as $key) {
                    Setting::set('payroll_' . $key, (string) (int) $data[$key]);
                }

                Notification::make()->title('Tarif & aturan disimpan')->success()->send();
            });
    }
}
