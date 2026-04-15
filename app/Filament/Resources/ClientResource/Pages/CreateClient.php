<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Enums\ClientAccessPolicy;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use N3XT0R\FilamentPassportUi\Resources\ClientResource;
use N3XT0R\LaravelPassportAuthorizationCore\Application\UseCases\Client\CreateClientUseCase;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Aplikasi')
                ->placeholder('Contoh: Aplikasi e-Rapot SDN 1')
                ->required()
                ->maxLength(255),

            TextInput::make('dashboard_url')
                ->label('Link Dashboard Aplikasi')
                ->placeholder('https://aplikasi-klien.test/dashboard')
                ->url()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set): void {
                    if (filled($state)) {
                        $set('redirect_uri', rtrim($state, '/').'/auth/callback');
                    }
                }),

            TextInput::make('redirect_uri')
                ->label('Redirect URI')
                ->helperText('Otomatis terisi dari Link Dashboard. Ubah jika memiliki URL callback berbeda.')
                ->url()
                ->required(),

            Select::make('access_policy')
                ->label('Kebijakan Akses')
                ->options(ClientAccessPolicy::class)
                ->default(ClientAccessPolicy::Restricted->value)
                ->required()
                ->helperText('Restricted: wajib ada rule cocok. Open: semua user aktif bisa masuk.'),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $actor = Filament::auth()->user();

        // Generate secret sendiri agar kita 100% yakin punya plain text-nya untuk disimpan
        $plainSecret = Str::random(40);

        $result = app(CreateClientUseCase::class)->execute(
            data: [
                'name' => $data['name'],
                'grant_type' => 'authorization_code',
                'owner' => $actor->getAuthIdentifier(),
                'redirect_uris' => [rtrim($data['redirect_uri'], '/')],
            ],
            actor: $actor,
        );

        $client = $result->client;
        $client->secret = $plainSecret;
        $client->plain_secret = $plainSecret;
        $client->access_policy = $data['access_policy'] ?? ClientAccessPolicy::Restricted->value;
        $client->save();

        return $client;
    }
}
