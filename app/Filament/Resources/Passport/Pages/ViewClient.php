<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Filament\Resources\Passport\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Js;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $plainSecret = $this->record->plain_secret ?: ($data['secret'] ?? '-');
        $redirectUris = (array) ($this->record->redirect_uris ?? []);
        $redirectUri = $redirectUris[0] ?? '';
        $clientId = (string) $this->record->getKey();
        $appName = (string) $this->record->name;
        $baseUrl = config('app.url');

        $data['id'] = $clientId;
        $data['secret'] = $plainSecret;
        $data['redirect_uri'] = $redirectUri;
        $data['access_policy_label'] = $this->record->access_policy?->label() ?? 'Open';

        $data['env_config'] = "# ==========================================\n".
            '# SIPETRA SSO CREDENTIALS - '.strtoupper($appName)."\n".
            "# ==========================================\n".
            "SIPETRA_CLIENT_ID=\"{$clientId}\"\n".
            "SIPETRA_CLIENT_SECRET=\"{$plainSecret}\"\n".
            "SIPETRA_REDIRECT_URI=\"{$redirectUri}\"\n".
            "SIPETRA_BASE_URL=\"{$baseUrl}\"";

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kredensial SSO Aplikasi')
                ->description('Gunakan Client ID dan Client Secret ini untuk menghubungkan aplikasi Anda ke server autentikasi SIPETRA.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('id')
                            ->label('Client ID')
                            ->disabled()
                            ->copyable()
                            ->helperText('Klik ikon salin di kanan input untuk menyalin ID.'),

                        TextInput::make('secret')
                            ->label('Client Secret')
                            ->password()
                            ->revealable()
                            ->disabled()
                            ->copyable()
                            ->helperText('Klik ikon mata untuk melihat atau ikon salin untuk meng-copy.'),

                        TextInput::make('name')
                            ->label('Nama Aplikasi')
                            ->disabled(),

                        TextInput::make('redirect_uri')
                            ->label('Redirect URI (OAuth Callback)')
                            ->disabled()
                            ->copyable(),

                        TextInput::make('access_policy_label')
                            ->label('Kebijakan Akses')
                            ->disabled(),
                    ]),
                ]),

            Section::make('Format File Konfigurasi (.env)')
                ->description('Salin blok konfigurasi berikut sekaligus untuk langsung ditempelkan ke file .env aplikasi klien Anda.')
                ->schema([
                    Textarea::make('env_config')
                        ->label('.ENV Configuration Snippet')
                        ->rows(6)
                        ->disabled()
                        ->extraInputAttributes([
                            'style' => 'font-family: monospace; font-size: 0.875rem; background: #0f172a; color: #38bdf8;',
                        ]),
                ]),

            Section::make('Panduan Integrasi Cepat')
                ->schema([
                    ViewField::make('credential_guide')
                        ->view('filament.passport.credential-guide')
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }

    public function getHeaderActions(): array
    {
        $clientId = (string) $this->record->getKey();
        $plainSecret = (string) ($this->record->plain_secret ?? '');
        $redirectUris = (array) ($this->record->redirect_uris ?? []);
        $redirectUri = $redirectUris[0] ?? '';
        $appName = (string) $this->record->name;
        $baseUrl = config('app.url');

        $envText = "# ==========================================\n".
            '# SIPETRA SSO CREDENTIALS - '.strtoupper($appName)."\n".
            "# ==========================================\n".
            "SIPETRA_CLIENT_ID=\"{$clientId}\"\n".
            "SIPETRA_CLIENT_SECRET=\"{$plainSecret}\"\n".
            "SIPETRA_REDIRECT_URI=\"{$redirectUri}\"\n".
            "SIPETRA_BASE_URL=\"{$baseUrl}\"";

        $jsText = Js::from($envText);

        $actions = [
            Action::make('copy_env')
                ->label('Salin .ENV')
                ->color('success')
                ->icon('heroicon-o-clipboard-document-check')
                ->extraAttributes([
                    'x-on:click' => "window.navigator.clipboard.writeText({$jsText}); \$tooltip('Format .ENV berhasil disalin ke clipboard!'); event.preventDefault();",
                ])
                ->action(fn () => null),
        ];

        // Jika redirect URI merupakan URL yang valid, tambahkan tombol buka aplikasi
        if (! empty($redirectUri) && filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($redirectUri);
            $originUrl = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '').(isset($parsed['port']) ? ':'.$parsed['port'] : '');

            $actions[] = Action::make('open_app')
                ->label('Buka Aplikasi')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url($originUrl)
                ->openUrlInNewTab();
        }

        $actions[] = EditAction::make();
        $actions[] = DeleteAction::make();

        return $actions;
    }
}
