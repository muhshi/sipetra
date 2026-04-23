<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas;

use App\Enums\ClientAccessPolicy;
use App\Filament\Passport\Schemas\Fields\ClientIdInput;
use App\Filament\Passport\Schemas\Fields\RedirectInput;
use App\Filament\Passport\Schemas\Fields\SecretInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use N3XT0R\FilamentPassportUi\Application\StateResolvers\GrantType\NeedsUserPermissionState;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\ClientResourceForm;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\GrantTypeSelect;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\NameInput;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\OwnerSelect;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\RevokeToggle;

class ExtendedClientResourceForm extends ClientResourceForm
{
    public static function configure(Schema $schema, array $additionalComponents = []): Schema
    {
        $components = [
            ClientIdInput::make(),
            NameInput::make(),
            OwnerSelect::make()
                ->requiredIf(
                    'grant_type',
                    fn (Get $get) => ! app(NeedsUserPermissionState::class)->execute($get('grant_type'))
                ),
            Select::make('access_policy')
                ->label('Access Policy')
                ->options(collect(ClientAccessPolicy::cases())->mapWithKeys(
                    fn (ClientAccessPolicy $policy): array => [$policy->value => $policy->label()]
                )->all())
                ->required(),
            GrantTypeSelect::make(),
            RedirectInput::make(),
            SecretInput::make(),
            RevokeToggle::make(),
        ];

        $dbScopesRequired = app(\N3XT0R\LaravelPassportAuthorizationCore\Repositories\ConfigRepository::class)->isUsingDatabaseScopes();

        if ($dbScopesRequired) {
            $components[] = \Filament\Schemas\Components\Grid::make()
                ->schema(fn (Get $get) => [
                    \N3XT0R\FilamentPassportUi\Resources\BaseResource\Components\ScopeCheckboxList::make(
                        context: 'client',
                        name: 'client_scopes',
                        record: $get('id') ? \App\Models\Passport\Client::find($get('id')) : null,
                        statePath: 'client_scopes',
                        contextClient: $get('id') ? \App\Models\Passport\Client::find($get('id')) : null,
                    ),
                ])
                ->key('client_scopes')
                ->columnSpanFull();
        }

        return $schema->components(
            array_merge(
                $components,
                $additionalComponents
            )
        );
    }
}
