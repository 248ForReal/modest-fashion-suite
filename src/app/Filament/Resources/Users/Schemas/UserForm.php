<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Payslip;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone')->tel(),
                DatePicker::make('birth_date'),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'active' => 'Active', 'inactive' => 'Inactive'])
                    ->default('pending')
                    ->required(),
                Select::make('roles')
                    ->label('Role')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->options(Role::all()->pluck('name', 'id'))
                    ->preload(),
                TextInput::make('password')
                    ->password()
                    ->nullable()
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->label('Password (kosongkan jika tidak diubah)'),

                // Data penggajian: hanya terlihat (dan tersimpan) oleh pengelola slip gaji
                Select::make('employment_type')
                    ->label('Tipe kerja (penggajian)')
                    ->options(['full_time' => 'Full time', 'part_time' => 'Part time'])
                    ->default('full_time')
                    ->required()
                    ->visible(fn () => Payslip::userCanManage(auth()->user())),
                TextInput::make('bank_name')
                    ->label('Bank')
                    ->maxLength(100)
                    ->visible(fn () => Payslip::userCanManage(auth()->user())),
                TextInput::make('bank_account')
                    ->label('No. Rekening')
                    ->maxLength(50)
                    ->visible(fn () => Payslip::userCanManage(auth()->user())),
                TextInput::make('default_allowance')
                    ->label('Tunjangan default per bulan')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('Rp')
                    ->visible(fn () => Payslip::userCanManage(auth()->user())),
            ]);
    }
}
