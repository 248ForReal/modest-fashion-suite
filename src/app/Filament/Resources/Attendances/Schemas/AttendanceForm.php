<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('type')
                    ->options([
            'clock_in' => 'Clock in',
            'clock_out' => 'Clock out',
            'leave' => 'Leave',
            'permission' => 'Permission',
        ])
                    ->required(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('distance_from_office')
                    ->numeric(),
                Toggle::make('is_valid')
                    ->required(),
                DateTimePicker::make('noted_at')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('photo'),
            ]);
    }
}
