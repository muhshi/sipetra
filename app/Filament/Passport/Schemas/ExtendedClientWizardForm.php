<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas;

use App\Filament\Passport\Schemas\Fields\RedirectInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use N3XT0R\FilamentPassportUi\Application\StateResolvers\GrantType\NeedsUserPermissionState;
use N3XT0R\FilamentPassportUi\Resources\BaseResource\Components\ScopeCheckboxList;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\ClientWizardForm;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\GrantTypeSelect;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\NameInput;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\OwnerSelect;
use N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client;

class ExtendedClientWizardForm extends ClientWizardForm
{
    public function getClientComponents(?Client $client = null): array
    {
        $dbScopesRequired = $this->configRepository->isUsingDatabaseScopes();

        $components = [
            NameInput::make()
                ->unique('oauth_clients', 'name', ignoreRecord: true),
            OwnerSelect::make()
                ->required(function (Get $get): bool {
                    $grantType = $get('grant_type');
                    if ($grantType === null) {
                        return false;
                    }

                    return app(NeedsUserPermissionState::class)->execute($grantType);
                }),
            RedirectInput::make(),
        ];

        if ($dbScopesRequired) {
            $components[] = Grid::make()
                ->schema(fn (Get $get) => [
                    ScopeCheckboxList::make(
                        context: 'client',
                        name: 'client_scopes',
                        record: $this->resolveClient($client, $get),
                        statePath: 'client_scopes',
                        contextClient: $this->resolveClient($client, $get),
                    ),
                ])
                ->key('client_scopes')
                ->columnSpanFull();
        }

        return [
            GrantTypeSelect::make('grant_type')
                ->live(),
            Grid::make()
                ->schema($components),
        ];
    }

    private function resolveClient(?Client $client, Get $get): ?Client
    {
        if ($id = $get('id')) {
            return Client::find($id);
        }

        return $client;
    }
}
