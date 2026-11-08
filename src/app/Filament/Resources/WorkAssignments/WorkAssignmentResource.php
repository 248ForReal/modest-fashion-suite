<?php

namespace App\Filament\Resources\WorkAssignments;

use App\Filament\Resources\WorkAssignments\Pages\CreateWorkAssignment;
use App\Filament\Resources\WorkAssignments\Pages\EditWorkAssignment;
use App\Filament\Resources\WorkAssignments\Pages\ListWorkAssignments;
use App\Filament\Resources\WorkAssignments\Schemas\WorkAssignmentForm;
use App\Filament\Resources\WorkAssignments\Tables\WorkAssignmentsTable;
use App\Filament\Traits\ChecksAdminMenuAccess;
use App\Models\WorkAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkAssignmentResource extends Resource
{
    use ChecksAdminMenuAccess;
    protected static string $adminMenuKey = 'work_assignments';
    protected static ?string $model = WorkAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Tugas Pekerjaan';

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Kerja';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Tugas';

    protected static ?string $pluralModelLabel = 'Tugas Pekerjaan';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->hasAnyRole(['super_admin', 'admin'])) return true;
        $role = $user->roles->first()?->name;
        if ($role && !\App\Filament\Pages\WorkSettings::canAccessMenu($role, 'work_assignments')) return false;
        return $user->canAny(['work.view_all', 'work.assign']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('work.assign') ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('work.assign') ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return WorkAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWorkAssignments::route('/'),
            'create' => CreateWorkAssignment::route('/create'),
            'edit'   => EditWorkAssignment::route('/{record}/edit'),
        ];
    }
}
