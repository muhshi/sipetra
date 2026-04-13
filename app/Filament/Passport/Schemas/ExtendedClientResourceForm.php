<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas;

use App\Filament\Passport\Schemas\Fields\RedirectInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use N3XT0R\FilamentPassportUi\Application\StateResolvers\GrantType\NeedsUserPermissionState;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\ClientResourceForm;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\GrantTypeSelect;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\IdHidden;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\NameInput;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\OwnerSelect;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\RevokeToggle;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\SecretInput;

class ExtendedClientResourceForm extends ClientResourceForm
{
    public static function configure(Schema $schema, array $additionalComponents = []): Schema
    {
        $components = [
            IdHidden::make(),
            NameInput::make(),
            OwnerSelect::make()
                ->requiredIf(
                    'grant_type',
                    fn (Get $get) => ! app(NeedsUserPermissionState::class)->execute($get('grant_type'))
                ),
            GrantTypeSelect::make(),
            RedirectInput::make(),
            SecretInput::make(),
            RevokeToggle::make(),
        ];

        return $schema->components(
            array_merge(
                $components,
                $additionalComponents
            )
        );
    }
}
