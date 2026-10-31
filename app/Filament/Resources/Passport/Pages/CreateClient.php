<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Enums\AccessRuleType;
use App\Enums\ClientAccessPolicy;
use App\Enums\IdentityType;
use App\Filament\Passport\Schemas\SimplifiedClientForm;
use App\Filament\Resources\Passport\ClientResource;
use App\Models\ClientAccessRule;
use App\Models\PortalApp;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use N3XT0R\FilamentPassportUi\Support\Cache\CacheFlasher;
use N3XT0R\LaravelPassportAuthorizationCore\Application\UseCases\Client\CreateClientUseCase;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public function form(Schema $schema): Schema
    {
        return SimplifiedClientForm::configure($schema);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $actor = Filament::auth()->user() ?? auth()->user();
        $name = trim((string) ($data['name'] ?? ''));
        $redirectUri = rtrim((string) ($data['redirect_uri'] ?? ''), '/');
        $plainSecret = Str::random(40);

        $defaultScopes = [
            'profile:read',
            'identity_pegawai:read',
            'identity_mitra:read',
            'employee:read',
            'contact:read',
            'roles:read',
        ];

        $scopes = ! empty($data['scopes']) ? (array) $data['scopes'] : $defaultScopes;

        $result = app(CreateClientUseCase::class)->execute(
            data: [
                'name' => $name,
                'grant_type' => 'authorization_code',
                'owner' => $actor?->getAuthIdentifier(),
                'redirect_uris' => [$redirectUri],
                'scopes' => $scopes,
            ],
            actor: $actor,
        );

        $client = $result->client;
        $resolvedSecret = $result->plainSecret ?: $plainSecret;

        DB::table('oauth_clients')
            ->where('id', $client->getKey())
            ->update(['plain_secret' => $resolvedSecret]);

        $client->plain_secret = $resolvedSecret;
        $client->save();

        // 1. Simpan Access Policy & Aturan Akses
        $preset = $data['access_preset'] ?? 'open';

        if ($preset === 'open') {
            $client->access_policy = ClientAccessPolicy::Open;
            $client->save();
        } elseif ($preset === 'pegawai') {
            $client->access_policy = ClientAccessPolicy::Restricted;
            $client->save();

            ClientAccessRule::create([
                'client_id' => $client->getKey(),
                'rule_type' => AccessRuleType::IdentityType,
                'rule_value' => IdentityType::Pegawai->value,
            ]);
        } elseif ($preset === 'mitra') {
            $client->access_policy = ClientAccessPolicy::Restricted;
            $client->save();

            ClientAccessRule::create([
                'client_id' => $client->getKey(),
                'rule_type' => AccessRuleType::IdentityType,
                'rule_value' => IdentityType::Mitra->value,
            ]);
        } else {
            // Preset Custom
            $client->access_policy = ClientAccessPolicy::Restricted;
            $client->save();

            if (! empty($data['access_rules'])) {
                foreach ($data['access_rules'] as $ruleSet) {
                    $ruleType = $ruleSet['rule_type'];
                    $ruleValues = (array) ($ruleSet['rule_values'] ?? []);

                    foreach ($ruleValues as $ruleValue) {
                        ClientAccessRule::create([
                            'client_id' => $client->getKey(),
                            'rule_type' => $ruleType,
                            'rule_value' => (string) $ruleValue,
                        ]);
                    }
                }
            }
        }

        // 2. Simpan cache secret untuk view page
        CacheFlasher::put(
            scope: 'passport.client.secret',
            key: $client->getKey(),
            value: $resolvedSecret,
        );
        session()->put('new_client_secret_'.$client->getKey(), $resolvedSecret);

        // 3. Integrasi Portal Landing Page jika dicentang
        if (! empty($data['add_to_portal'])) {
            $portalUrl = ! empty($data['app_url']) ? $data['app_url'] : $redirectUri;

            PortalApp::create([
                'name' => $client->name,
                'full_name' => $data['portal_full_name'] ?? null,
                'description' => $data['portal_description'] ?? null,
                'url' => $portalUrl,
                'logo' => $data['portal_logo'] ?? null,
                'icon' => $data['portal_icon'] ?? 'heroicon-o-squares-2x2',
                'is_active' => true,
                'order' => (int) ($data['portal_order'] ?? 0),
            ]);
        }

        return $client;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
