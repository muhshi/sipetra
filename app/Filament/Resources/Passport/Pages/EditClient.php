<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Filament\Resources\Passport\ClientResource;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\EditClient as BaseEditClient;

class EditClient extends BaseEditClient
{
    protected static string $resource = ClientResource::class;
}
