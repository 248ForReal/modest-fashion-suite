<?php

namespace App\Filament\Resources\ProductSizes\Pages;

use App\Filament\Resources\ProductSizes\ProductSizeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSize extends CreateRecord
{
    protected static string $resource = ProductSizeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
