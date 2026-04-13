<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Filament\Passport\Schemas\ExtendedClientWizardForm;
use App\Filament\Resources\Passport\ClientResource;
use Filament\Schemas\Schema;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\CreateClient as BaseCreateClient;

class CreateClient extends BaseCreateClient
{
    protected static string $resource = ClientResource::class;

    public function form(Schema $schema): Schema
    {
        return ExtendedClientWizardForm::configure($schema);
    }
}
