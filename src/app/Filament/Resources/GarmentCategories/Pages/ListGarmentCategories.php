<?php

namespace App\Filament\Resources\GarmentCategories\Pages;

use App\Filament\Resources\GarmentCategories\GarmentCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGarmentCategories extends ListRecords
{
    protected static string $resource = GarmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Kategori')];
    }
}
