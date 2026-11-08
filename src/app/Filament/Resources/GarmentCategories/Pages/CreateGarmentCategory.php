<?php

namespace App\Filament\Resources\GarmentCategories\Pages;

use App\Filament\Resources\GarmentCategories\GarmentCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGarmentCategory extends CreateRecord
{
    protected static string $resource = GarmentCategoryResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
    protected function getCreatedNotificationTitle(): ?string { return 'Kategori berhasil ditambahkan'; }
}
