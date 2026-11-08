<?php

namespace App\Filament\Resources\WorkAssignments\Pages;

use App\Filament\Resources\WorkAssignments\WorkAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkAssignment extends EditRecord
{
    protected static string $resource = WorkAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotificationTitle('Tugas pekerjaan berhasil dihapus'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Tugas pekerjaan berhasil diupdate';
    }
}
