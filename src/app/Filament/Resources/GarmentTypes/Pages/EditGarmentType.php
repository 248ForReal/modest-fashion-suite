<?php

namespace App\Filament\Resources\GarmentTypes\Pages;

use App\Filament\Resources\GarmentTypes\GarmentTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGarmentType extends EditRecord
{
    protected static string $resource = GarmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotificationTitle('Jenis pakaian berhasil dihapus'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Jenis pakaian berhasil diupdate';
    }
}
