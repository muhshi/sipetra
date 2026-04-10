<?php

namespace App\Filament\Resources\OAuthClients\Schemas;

use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Laravel\Passport\Passport;

class OAuthClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client Details')
                    ->icon('heroicon-o-key')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Client')
                            ->placeholder('Contoh: Aplikasi Survei BPS')
                            ->helperText('Nama aplikasi yang akan tampil di halaman consent SSO.')
                            ->required()
                            ->maxLength(255),

                        Select::make('grant_type')
                            ->label('Grant Type')
                            ->options([
                                'authorization_code' => 'Authorization Code (SSO)',
                                'client_credentials' => 'Client Credentials (M2M)',
                                'personal_access' => 'Personal Access Token',
                            ])
                            ->required()
                            ->live()
                            ->default('authorization_code')
                            ->helperText('Pilih "Authorization Code" untuk SSO standar.'),

                        Select::make('owner_id')
                            ->label('Owner')
                            ->options(fn (): array => User::query()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->helperText('User pemilik client ini.')
                            ->visible(fn (Get $get): bool => in_array($get('grant_type'), ['authorization_code', 'personal_access'])),
                    ]),

                Section::make('Redirect URIs')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->description('URL callback yang diizinkan untuk redirect setelah OAuth authorization. Minimal satu URI wajib diisi.')
                    ->visible(fn (Get $get): bool => $get('grant_type') === 'authorization_code')
                    ->schema([
                        TagsInput::make('redirect_uris')
                            ->label('Redirect URIs')
                            ->placeholder('Ketik URL lalu tekan Enter')
                            ->helperText('Contoh: http://localhost:8001/auth/callback')
                            ->required(fn (Get $get): bool => $get('grant_type') === 'authorization_code')
                            ->nestedRecursiveRules(['url']),
                    ]),

                Section::make('Scopes')
                    ->icon('heroicon-o-shield-check')
                    ->description('Scope membatasi akses data yang diberikan ke client ini.')
                    ->collapsible()
                    ->schema(
                        static::getScopeCheckboxes()
                    ),

                Section::make('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Toggle::make('confidential')
                            ->label('Confidential Client')
                            ->helperText('Client dengan secret. Nonaktifkan untuk SPA/mobile (public client).')
                            ->default(true),

                        Toggle::make('revoked')
                            ->label('Revoked')
                            ->helperText('Nonaktifkan client ini. Token yang sudah diterbitkan tidak akan bisa digunakan.')
                            ->default(false),
                    ]),
            ]);
    }

    /**
     * Generate checkbox list from Passport::tokensCan() scopes.
     *
     * @return array<Component>
     */
    protected static function getScopeCheckboxes(): array
    {
        $scopes = Passport::scopes();

        if ($scopes->isEmpty()) {
            return [
                Placeholder::make('no_scopes')
                    ->content('Belum ada scope yang didefinisikan.'),
            ];
        }

        return [
            CheckboxList::make('selected_scopes')
                ->label('Pilih Scopes')
                ->options(
                    $scopes->mapWithKeys(fn ($scope) => [$scope->id => "{$scope->id} — {$scope->description}"])->toArray()
                )
                ->columns(2)
                ->helperText('Scope yang dipilih akan membatasi data yang bisa diakses oleh client.'),
        ];
    }
}
