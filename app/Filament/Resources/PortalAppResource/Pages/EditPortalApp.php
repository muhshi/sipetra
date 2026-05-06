<?php

namespace App\Filament\Resources\PortalAppResource\Pages;

use App\Filament\Resources\PortalAppResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortalApp extends EditRecord
{
    protected static string $resource = PortalAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
