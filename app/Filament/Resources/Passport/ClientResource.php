<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport;

use App\Filament\Passport\Schemas\ExtendedClientResourceForm;
use Filament\Schemas\Schema;
use N3XT0R\FilamentPassportUi\Resources\ClientResource as BaseClientResource;

class ClientResource extends BaseClientResource
{
    protected static ?string $slug = 'clients';

    public static function form(Schema $schema): Schema
    {
        return ExtendedClientResourceForm::configure($schema);
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
