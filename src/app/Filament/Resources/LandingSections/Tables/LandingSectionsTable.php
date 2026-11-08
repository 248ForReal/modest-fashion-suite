<?php

namespace App\Filament\Resources\LandingSections\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LandingSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('urutan')
            ->defaultSort('urutan')
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->width(1),

                TextColumn::make('key')
                    ->label('Section')
                    ->badge()
                    ->searchable(),

                TextColumn::make('title_en')
                    ->label('Judul (EN)')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('display_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'carousel' => 'warning',
                        'grid'     => 'info',
                        default    => 'gray',
                    }),

                TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
