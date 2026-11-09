<?php

namespace App\Filament\Resources\Payslips;

use App\Filament\Resources\Payslips\Pages\EditPayslip;
use App\Filament\Resources\Payslips\Pages\ListPayslips;
use App\Filament\Resources\Payslips\Schemas\PayslipForm;
use App\Filament\Resources\Payslips\Tables\PayslipsTable;
use App\Filament\Traits\ChecksAdminMenuAccess;
use App\Models\Payslip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PayslipResource extends Resource
{
    use ChecksAdminMenuAccess;

    protected static string $adminMenuKey = 'payslips';
    protected static ?string $model = Payslip::class;
    protected static ?string $navigationLabel = 'Slip Gaji';
    protected static ?string $modelLabel = 'Slip Gaji';
    protected static ?string $pluralModelLabel = 'Slip Gaji';
    protected static \UnitEnum|string|null $navigationGroup = 'Penggajian';
    protected static ?int $navigationSort = 1;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function form(Schema $schema): Schema
    {
        return PayslipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayslipsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    /** Slip hanya dibuat lewat tombol "Buat Slip Periode". */
    public static function canCreate(): bool
    {
        return false;
    }

    /** Slip final terkunci; buka kunci dulu bila perlu diubah. */
    public static function canEdit(Model $record): bool
    {
        return ! $record->isFinal();
    }

    public static function canDelete(Model $record): bool
    {
        return ! $record->isFinal();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayslips::route('/'),
            'edit'  => EditPayslip::route('/{record}/edit'),
        ];
    }
}
