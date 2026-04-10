<?php

namespace App\Filament\Resources\OAuthClients\Pages;

use App\Filament\Resources\OAuthClients\OAuthClientResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

class CreateOAuthClient extends CreateRecord
{
    protected static string $resource = OAuthClientResource::class;

    /**
     * Store the plain-text secret after creation so we can display it.
     */
    protected ?string $plainSecret = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $grantType = $data['grant_type'] ?? 'authorization_code';

        $data['grant_types'] = match ($grantType) {
            'authorization_code' => ['authorization_code', 'refresh_token'],
            'client_credentials' => ['client_credentials'],
            'personal_access' => ['personal_access'],
            default => [$grantType],
        };

        $isConfidential = $data['confidential'] ?? true;

        if ($isConfidential) {
            $data['secret'] = hash('sha256', $plainSecret = Str::random(40));
            $this->plainSecret = $plainSecret;
        }

        if ($grantType === 'client_credentials') {
            $data['owner_id'] = null;
            $data['owner_type'] = null;
        } else {
            $data['owner_type'] = (new (Passport::$userModel ?? User::class))->getMorphClass();
        }

        $data['redirect_uris'] = $data['redirect_uris'] ?? [];

        unset($data['grant_type'], $data['confidential'], $data['selected_scopes']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->plainSecret) {
            session()->flash('oauth_client_secret', $this->plainSecret);
            session()->flash('oauth_client_id', $this->record->getKey());
        }
    }

    protected function getRedirectUrl(): string
    {
        return OAuthClientResource::getUrl('view', ['record' => $this->record]);
    }
}
