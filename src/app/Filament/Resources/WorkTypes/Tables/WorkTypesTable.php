<?php

namespace App\Filament\Resources\WorkTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Pekerjaan')
                    ->searchable(),
                TextColumn::make('kategori_nilai')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'jahit'  => 'info',
                        'desain' => 'warning',
                        'pola'   => 'success',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('bobot_persen')
                    ->label('Bobot (%)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('kategori_nilai')
                    ->label('Kategori')
                    ->options([
                        'jahit'  => 'Jahit',
                        'desain' => 'Desain',
                        'pola'   => 'Pola',
                    ]),
            ])
            ->defaultSort('kategori_nilai')
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Jenis pekerjaan berhasil diupdate'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
