<?php

namespace App\Filament\Resources\SsoAuditLogResource\Pages;

use App\Filament\Resources\SsoAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSsoAuditLogs extends ListRecords
{
    protected static string $resource = SsoAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
