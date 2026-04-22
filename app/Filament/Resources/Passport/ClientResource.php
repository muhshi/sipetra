<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport;

use App\Filament\Passport\Schemas\ExtendedClientResourceForm;
use App\Filament\Resources\Passport\RelationManagers\AccessRulesRelationManager;
use App\Filament\Resources\Passport\RelationManagers\ClientTokensRelationManager;
use Filament\Schemas\Schema;
use N3XT0R\FilamentPassportUi\Resources\ClientResource as BaseClientResource;
use UnitEnum;

class ClientResource extends BaseClientResource
{
    protected static ?string $slug = 'clients';

    protected static string|UnitEnum|null $navigationGroup = 'SSO';

    public static function form(Schema $schema): Schema
    {
        return ExtendedClientResourceForm::configure($schema);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'SSO';
    }

    public static function getRelations(): array
    {
        return [
            ClientTokensRelationManager::class,
            AccessRulesRelationManager::class,
            RelationManagers\PassportScopeGrantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
        ];
    }
}
