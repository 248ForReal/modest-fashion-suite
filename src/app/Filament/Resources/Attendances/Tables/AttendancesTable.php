<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Exports\AttendanceExport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'clock_in'   => 'Masuk',
                        'clock_out'  => 'Keluar',
                        'leave'      => 'Cuti',
                        'permission' => 'Izin',
                        default      => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'clock_in'   => 'success',
                        'clock_out'  => 'gray',
                        'leave'      => 'warning',
                        'permission' => 'info',
                        default      => 'gray',
                    }),
                TextColumn::make('distance_from_office')
                    ->label('Jarak (m)')
                    ->numeric()
                    ->sortable()
                    ->hiddenOn('sm'),
                IconColumn::make('is_valid')
                    ->label('Valid')
                    ->boolean()
                    ->hiddenOn('sm'),
                TextColumn::make('noted_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable()
                    ->hiddenOn('sm'),
            ])
            ->defaultSort('noted_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'clock_in'   => 'Masuk',
                        'clock_out'  => 'Keluar',
                        'leave'      => 'Cuti',
                        'permission' => 'Izin',
                    ]),
                SelectFilter::make('is_valid')
                    ->label('Status')
                    ->options([1 => 'Valid', 0 => 'Tidak Valid']),
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'], fn($q, $v) => $q->whereDate('noted_at', '>=', $v))
                            ->when($data['sampai'], fn($q, $v) => $q->whereDate('noted_at', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'] ?? null) $indicators[] = 'Dari: ' . $data['dari'];
                        if ($data['sampai'] ?? null) $indicators[] = 'Sampai: ' . $data['sampai'];
                        return $indicators;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Absensi berhasil diupdate'),
                DeleteAction::make()
                    ->requiresConfirmation(false)
                    ->successNotificationTitle('Absensi berhasil dihapus')
                    ->visible(fn() => auth()->user()?->hasRole('super_admin')),
            ])
            ->toolbarActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        DatePicker::make('dari')->label('Dari Tanggal'),
                        DatePicker::make('sampai')->label('Sampai Tanggal'),
                        \Filament\Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'clock_in'   => 'Masuk',
                                'clock_out'  => 'Keluar',
                                'leave'      => 'Cuti',
                                'permission' => 'Izin',
                            ])
                            ->placeholder('Semua'),
                    ])
                    ->action(fn(array $data) => redirect()->away(
                        route('export.attendances', array_filter([
                            'dari'   => $data['dari'] ?? null,
                            'sampai' => $data['sampai'] ?? null,
                            'type'   => $data['type'] ?? null,
                        ]))
                    )),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(false)
                        ->visible(fn() => auth()->user()?->hasRole('super_admin')),
                ]),
            ]);
    }
}
