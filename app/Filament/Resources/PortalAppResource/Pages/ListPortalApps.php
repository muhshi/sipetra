<?php

namespace App\Filament\Resources\PortalAppResource\Pages;

use App\Filament\Resources\PortalAppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortalApps extends ListRecords
{
    protected static string $resource = PortalAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
