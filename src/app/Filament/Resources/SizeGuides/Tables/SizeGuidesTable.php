<?php

namespace App\Filament\Resources\SizeGuides\Tables;

use App\Models\GarmentCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SizeGuidesTable
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

                TextColumn::make('productSize.nama')
                    ->label('Ukuran')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('dimensi')
                    ->label('Dimensi')
                    ->formatStateUsing(function ($state) {
                        if (!$state || !is_array($state)) return '—';
                        $parts = [];
                        foreach ($state as $k => $v) {
                            $label = str_replace('_', ' ', $k);
                            $parts[] = "{$label}: {$v}cm";
                        }
                        return implode(' | ', $parts);
                    })
                    ->limit(80)
                    ->tooltip(function ($state) {
                        if (!$state || !is_array($state)) return null;
                        $parts = [];
                        foreach ($state as $k => $v) {
                            $label = str_replace('_', ' ', $k);
                            $parts[] = "{$label}: {$v}cm";
                        }
                        return implode("\n", $parts);
                    }),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('garment_category_id')
                    ->label('Kategori')
                    ->options(fn () => GarmentCategory::orderBy('nama')->pluck('nama', 'id')->toArray()),
            ])
            ->defaultSort('garment_category_id')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
