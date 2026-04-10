<?php

namespace App\Filament\Resources\OAuthClients\Pages;

use App\Filament\Resources\OAuthClients\OAuthClientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOAuthClient extends EditRecord
{
    protected static string $resource = OAuthClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $grantTypes = $data['grant_types'] ?? [];

        $data['grant_type'] = match (true) {
            in_array('authorization_code', $grantTypes) => 'authorization_code',
            in_array('client_credentials', $grantTypes) => 'client_credentials',
            in_array('personal_access', $grantTypes) => 'personal_access',
            default => $grantTypes[0] ?? 'authorization_code',
        };

        $data['confidential'] = filled($data['secret'] ?? null);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $grantType = $data['grant_type'] ?? 'authorization_code';

        $data['grant_types'] = match ($grantType) {
            'authorization_code' => ['authorization_code', 'refresh_token'],
            'client_credentials' => ['client_credentials'],
            'personal_access' => ['personal_access'],
            default => [$grantType],
        };

        $data['redirect_uris'] = $data['redirect_uris'] ?? [];

        unset($data['grant_type'], $data['confidential'], $data['selected_scopes']);

        return $data;
    }

    protected function getRedirectUrl(): ?string
    {
        return OAuthClientResource::getUrl('view', ['record' => $this->record]);
    }
}
