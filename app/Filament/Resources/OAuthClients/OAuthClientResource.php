<?php

namespace App\Filament\Resources\OAuthClients;

use App\Filament\Resources\OAuthClients\Pages\CreateOAuthClient;
use App\Filament\Resources\OAuthClients\Pages\EditOAuthClient;
use App\Filament\Resources\OAuthClients\Pages\ListOAuthClients;
use App\Filament\Resources\OAuthClients\Pages\ViewOAuthClient;
use App\Filament\Resources\OAuthClients\Schemas\OAuthClientForm;
use App\Filament\Resources\OAuthClients\Tables\OAuthClientsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Passport\Client;

class OAuthClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static \UnitEnum|string|null $navigationGroup = 'OAuth Management';

    protected static ?string $navigationLabel = 'OAuth Clients';

    protected static ?string $modelLabel = 'OAuth Client';

    protected static ?string $pluralModelLabel = 'OAuth Clients';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return OAuthClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OAuthClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOAuthClients::route('/'),
            'create' => CreateOAuthClient::route('/create'),
            'view' => ViewOAuthClient::route('/{record}'),
            'edit' => EditOAuthClient::route('/{record}/edit'),
        ];
    }
}
