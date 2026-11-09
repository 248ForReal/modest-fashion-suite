<?php

namespace App\Filament\Resources\Payslips\Tables;

use App\Models\Payslip;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PayslipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('period', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Karyawan')->searchable()->sortable(),
                TextColumn::make('period')
                    ->label('Periode')
                    ->formatStateUsing(fn (string $state): string => Payslip::periodLabelFor($state))
                    ->sortable(),
                TextColumn::make('work_days')->label('Hari Kerja')->alignCenter(),
                TextColumn::make('garment_count')->label('Jlh Baju')->alignCenter(),
                TextColumn::make('total_income')
                    ->label('Total Pendapatan')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . Payslip::rupiah($state, false))
                    ->alignEnd(),
                TextColumn::make('take_home_pay')
                    ->label('Take Home Pay')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . Payslip::rupiah($state, false))
                    ->weight('bold')
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === Payslip::STATUS_FINAL ? 'Final' : 'Draft')
                    ->color(fn (string $state): string => $state === Payslip::STATUS_FINAL ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('period')
                    ->label('Periode')
                    ->options(fn (): array => Payslip::query()
                        ->select('period')->distinct()->orderByDesc('period')->pluck('period')
                        ->mapWithKeys(fn (string $p): array => [$p => Payslip::periodLabelFor($p)])
                        ->all()),
                SelectFilter::make('status')
                    ->options([Payslip::STATUS_DRAFT => 'Draft', Payslip::STATUS_FINAL => 'Final']),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('cetak')
                    ->label('Cetak')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->url(fn (Payslip $record): string => route('payslips.print', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
                Action::make('finalkan')
                    ->label('Finalkan')
                    ->icon(Heroicon::OutlinedLockClosed)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Slip final terkunci dan langsung bisa dilihat karyawan yang bersangkutan.')
                    ->visible(fn (?Payslip $record): bool => $record !== null && ! $record->isFinal())
                    ->action(function (Payslip $record): void {
                        self::finalize($record);
                        Notification::make()->title('Slip difinalkan')->success()->send();
                    }),
                Action::make('bukaKunci')
                    ->label('Buka Kunci')
                    ->icon(Heroicon::OutlinedLockOpen)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Slip kembali menjadi draft dan tidak terlihat oleh karyawan sampai difinalkan lagi.')
                    ->visible(fn (?Payslip $record): bool => $record !== null && $record->isFinal())
                    ->action(function (Payslip $record): void {
                        $record->update(['status' => Payslip::STATUS_DRAFT, 'finalized_at' => null]);
                        Notification::make()->title('Slip dibuka kembali sebagai draft')->success()->send();
                    }),
                DeleteAction::make()->visible(fn (?Payslip $record): bool => $record !== null && ! $record->isFinal()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('cetakTerpilih')
                        ->label('Cetak terpilih')
                        ->icon(Heroicon::OutlinedPrinter)
                        ->action(function (Collection $records, $livewire): void {
                            $livewire->redirect(route('payslips.print', ['ids' => $records->pluck('id')->implode(',')]));
                        }),
                    BulkAction::make('finalkanTerpilih')
                        ->label('Finalkan terpilih')
                        ->icon(Heroicon::OutlinedLockClosed)
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if (! $record->isFinal()) {
                                    self::finalize($record);
                                    $count++;
                                }
                            }
                            Notification::make()->title("{$count} slip difinalkan")->success()->send();
                        }),
                ]),
            ]);
    }

    public static function finalize(Payslip $slip): void
    {
        $slip->update(['status' => Payslip::STATUS_FINAL, 'finalized_at' => now()]);
    }
}
