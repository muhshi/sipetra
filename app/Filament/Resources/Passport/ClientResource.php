<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport;

use App\Enums\ClientAccessPolicy;
use App\Filament\Passport\Schemas\ExtendedClientResourceForm;
use App\Filament\Resources\Passport\RelationManagers\AccessRulesRelationManager;
use App\Filament\Resources\Passport\RelationManagers\ClientTokensRelationManager;
use App\Models\Passport\Client;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Js;
use N3XT0R\FilamentPassportUi\Resources\ClientResource as BaseClientResource;
use UnitEnum;

class ClientResource extends BaseClientResource
{
    protected static ?string $model = Client::class;

    protected static ?string $slug = 'clients';

    protected static string|UnitEnum|null $navigationGroup = 'SSO';

    public static function form(Schema $schema): Schema
    {
        return ExtendedClientResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Aplikasi')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(function (Client $record): ?string {
                        $uris = (array) ($record->redirect_uris ?? []);

                        return $uris[0] ?? null;
                    }),

                TextColumn::make('id')
                    ->label('Client ID')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable()
                    ->limit(16),

                TextColumn::make('access_policy')
                    ->label('Akses')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        ClientAccessPolicy::Open => 'success',
                        ClientAccessPolicy::Restricted => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state instanceof ClientAccessPolicy ? $state->label() : ucfirst((string) $state)),

                IconColumn::make('revoked')
                    ->label('Aktif')
                    ->boolean()
                    ->state(fn (Client $record): bool => ! $record->revoked)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('copy_env')
                    ->label('Salin .ENV')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->extraAttributes(function (Client $record): array {
                        $clientId = (string) $record->getKey();
                        $plainSecret = (string) ($record->plain_secret ?? '');
                        $uris = (array) ($record->redirect_uris ?? []);
                        $redirectUri = $uris[0] ?? '';
                        $appName = (string) $record->name;
                        $baseUrl = config('app.url');

                        $envText = "# ==========================================\n".
                            '# SIPETRA SSO CREDENTIALS - '.strtoupper($appName)."\n".
                            "# ==========================================\n".
                            "SIPETRA_CLIENT_ID=\"{$clientId}\"\n".
                            "SIPETRA_CLIENT_SECRET=\"{$plainSecret}\"\n".
                            "SIPETRA_REDIRECT_URI=\"{$redirectUri}\"\n".
                            "SIPETRA_BASE_URL=\"{$baseUrl}\"";

                        $jsText = Js::from($envText);

                        return [
                            'x-on:click' => "window.navigator.clipboard.writeText({$jsText}); \$tooltip('Format .ENV berhasil disalin!'); event.preventDefault();",
                        ];
                    })
                    ->action(fn () => null),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
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
