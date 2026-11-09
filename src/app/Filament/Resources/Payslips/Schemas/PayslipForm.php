<?php

namespace App\Filament\Resources\Payslips\Schemas;

use App\Models\Payslip;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class PayslipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Karyawan & Periode')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        Text::make(fn (?Payslip $record): string => $record
                            ? $record->user->name . ' — ' . $record->periodLabel()
                            : '-'),
                    ]),

                Section::make('Kehadiran & Hasil')
                    ->description('Diisi otomatis dari Absensi dan Tugas yang disetujui. Boleh dikoreksi.')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        self::count('work_days', 'Hari Kerja (hadir)'),
                        self::count('absent_days', 'Hari Tidak Hadir'),
                        self::count('garment_count', 'Jumlah Baju'),
                    ]),

                Section::make('A. Benefit')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        self::money('piece_earnings', 'Hasil Kerja')
                            ->helperText('Diisi manual oleh admin.'),
                        self::money('meal_allowance', 'Uang Makan'),
                        self::money('allowance', 'Tunjangan'),
                        self::money('performance_bonus', 'Bonus Kinerja'),
                    ]),

                Section::make('B. Bantuan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        self::money('employer_bpjs', 'BPJS TK (ditanggung perusahaan)')
                            ->helperText('Hanya informasi di slip, tidak masuk Total Pendapatan.'),
                        self::money('other_bonus', 'Bonus Lainnya'),
                        self::money('pocket_money', 'Jajan'),
                    ]),

                Section::make('C. Lainnya & Pengurangan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        self::money('other_income', 'Lainnya (C)'),
                        self::money('deduction', 'Pengurangan')
                            ->helperText('Otomatis: hari tidak hadir × tarif jajan. Boleh dikoreksi.'),
                    ]),

                Section::make('Total')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        // Dihitung di browser agar langsung berubah saat angka diketik.
                        // Nilai yang disimpan tetap dihitung ulang di server (Payslip::refreshTotals).
                        Text::make(<<<'JS'
                            (() => {
                                const n = (k) => Number($get(k) || 0);
                                const income = n('piece_earnings') + n('meal_allowance') + n('allowance')
                                    + n('performance_bonus') + n('other_bonus') + n('pocket_money') + n('other_income');
                                const f = (v) => new Intl.NumberFormat('id-ID').format(v);
                                return `Total Pendapatan: Rp ${f(income)}   |   Take Home Pay: Rp ${f(income - n('deduction'))}`;
                            })()
                            JS)
                            ->js()
                            ->weight('bold'),
                    ]),

                Section::make('Transfer')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('bank_name')->label('Bank')->maxLength(100),
                        TextInput::make('bank_account')->label('No. Rekening')->maxLength(50),
                        DatePicker::make('transfer_date')->label('Tanggal Transfer')->native(false)->displayFormat('d/m/Y'),
                    ]),
            ]);
    }

    private static function money(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->default(0)
            ->required()
            ->prefix('Rp');
    }

    private static function count(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->integer()
            ->minValue(0)
            ->default(0)
            ->required();
    }
}
