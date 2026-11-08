<?php

namespace App\Filament\Resources\GarmentTypes\Pages;

use App\Filament\Resources\GarmentTypes\GarmentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGarmentTypes extends ListRecords
{
    protected static string $resource = GarmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
