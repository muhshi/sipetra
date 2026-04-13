<?php

declare(strict_types=1);

namespace App\Filament\Plugins;

use App\Filament\Resources\Passport\ClientResource;
use Filament\Panel;
use N3XT0R\FilamentPassportUi\FilamentPassportUiPlugin;
use N3XT0R\FilamentPassportUi\Resources\PassportScopeActionsResource;
use N3XT0R\FilamentPassportUi\Resources\PassportScopeResourceResource;
use N3XT0R\FilamentPassportUi\Resources\TokenResource;

class ExtendedPassportUiPlugin extends FilamentPassportUiPlugin
{
    protected function registerResources(Panel $panel): void
    {
        $resources = [
            ClientResource::class, // Use our extended resource
            TokenResource::class,
        ];

        if (config('filament-passport-ui.enable_scopes_management', true)) {
            $resources[] = PassportScopeResourceResource::class;
            $resources[] = PassportScopeActionsResource::class;
        }

        $panel->resources($resources);
    }
}
