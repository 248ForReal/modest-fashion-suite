<?php

namespace App\Filament\Resources\GarmentTypes\Tables;

use App\Models\GarmentCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GarmentTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('garmentCategory.nama')
                    ->label('Kategori')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sub_kategori')
                    ->label('Sub Kategori')
                    ->searchable()
                    ->hiddenOn('sm'),
                TextColumn::make('contoh_pakaian')
                    ->label('Contoh Pakaian')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('nilai_jahit')
                    ->label('Jahit')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->hiddenOn('sm'),
                TextColumn::make('nilai_desain')
                    ->label('Desain')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->hiddenOn('sm'),
                TextColumn::make('nilai_pola')
                    ->label('Pola')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->hiddenOn('sm'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('garment_category_id')
                    ->label('Kategori')
                    ->options(fn () => GarmentCategory::orderBy('nama')->pluck('nama', 'id')->toArray()),
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->defaultSort('sub_kategori')
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Jenis pakaian berhasil diupdate'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
