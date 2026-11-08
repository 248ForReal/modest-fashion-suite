<?php

namespace App\Filament\Resources\GarmentTypes\Pages;

use App\Filament\Resources\GarmentTypes\GarmentTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGarmentType extends CreateRecord
{
    protected static string $resource = GarmentTypeResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Jenis pakaian berhasil dibuat';
    }
}
