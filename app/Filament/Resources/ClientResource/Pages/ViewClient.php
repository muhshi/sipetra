<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;
use N3XT0R\FilamentPassportUi\Resources\ClientResource;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Actions\DeleteAction;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    /**
     * Ambil plain_secret langsung dari DB untuk menghindari masalah $hidden pada model Eloquent.
     */
    private function getPlainSecret(): ?string
    {
        return DB::table('oauth_clients')
            ->where('id', $this->record->getKey())
            ->value('plain_secret');
    }

    private function getRedirectUri(): string
    {
        $uris = DB::table('oauth_clients')
            ->where('id', $this->record->getKey())
            ->value('redirect_uris');

        $decoded = is_string($uris) ? json_decode($uris, true) : $uris;

        return is_array($decoded) && count($decoded) > 0 ? $decoded[0] : '';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $plainSecret = $this->getPlainSecret();
        $redirectUri = $this->getRedirectUri();
        $clientId = $this->record->getKey();

        $data['secret'] = $plainSecret;
        $data['redirect_uri'] = $redirectUri;
        $data['env_config'] = "SIPETRA_CLIENT_ID=\"{$clientId}\"\n".
                               "SIPETRA_CLIENT_SECRET=\"{$plainSecret}\"\n".
                               "SIPETRA_REDIRECT_URI=\"{$redirectUri}\"";

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Credential Info')
                ->description('Gunakan Client ID dan Client Secret di bawah ini untuk menghubungkan aplikasi Anda ke Sipetra.')
                ->schema([
                    TextInput::make('id')
                        ->label('Client ID')
                        ->disabled()
                        ->copyable(),

                    TextInput::make('secret')
                        ->label('Client Secret')
                        ->disabled()
                        ->copyable(),
                ])->columns(2),

            Section::make('Config Format (.env)')
                ->description('Salin blok konfigurasi ini sekaligus untuk ditempel ke file .env aplikasi klien Anda.')
                ->schema([
                    Textarea::make('env_config')
                        ->label('.ENV Configuration')
                        ->disabled()
                        ->rows(4)
                        ->extraInputAttributes(['style' => 'font-family: monospace; cursor: text;']),
                ])->columns(1),

            Section::make('Client Details')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Aplikasi')
                        ->disabled(),

                    TextInput::make('redirect_uri')
                        ->label('Redirect URI')
                        ->disabled()
                        ->copyable(),
                ])->columns(2),
        ]);
    }

    public function getHeaderActions(): array
    {
        $clientId = $this->record->getKey();
        $plainSecret = $this->getPlainSecret();
        $redirectUri = $this->getRedirectUri();

        $envText = "SIPETRA_CLIENT_ID=\"{$clientId}\"\n".
                   "SIPETRA_CLIENT_SECRET=\"{$plainSecret}\"\n".
                   "SIPETRA_REDIRECT_URI=\"{$redirectUri}\"";

        $jsText = Js::from($envText);

        return [
            Action::make('copyEnv')
                ->label('Copy .ENV')
                ->color('success')
                ->icon('heroicon-o-clipboard-document-list')
                ->extraAttributes([
                    'x-on:click' => "window.navigator.clipboard.writeText({$jsText}); \$tooltip('Format .ENV berhasil disalin!'); event.preventDefault();",
                ])
                ->action(fn () => null),

            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
