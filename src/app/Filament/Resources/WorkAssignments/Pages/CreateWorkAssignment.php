<?php

namespace App\Filament\Resources\WorkAssignments\Pages;

use App\Filament\Resources\WorkAssignments\WorkAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkAssignment extends CreateRecord
{
    protected static string $resource = WorkAssignmentResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Tugas pekerjaan berhasil dibuat';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status']     = 'open';
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
