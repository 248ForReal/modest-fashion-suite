<?php

namespace App\Filament\Resources\SizeGuides\Pages;

use App\Filament\Resources\SizeGuides\SizeGuideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSizeGuide extends CreateRecord
{
    protected static string $resource = SizeGuideResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
