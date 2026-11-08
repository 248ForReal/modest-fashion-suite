<?php

namespace App\Filament\Resources\ProductSizes\Pages;

use App\Filament\Resources\ProductSizes\ProductSizeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductSizes extends ListRecords
{
    protected static string $resource = ProductSizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
