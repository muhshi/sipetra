<?php

use App\Enums\ClientAccessPolicy;
use App\Models\Passport\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeAction;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeGrant;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeResource;

uses(RefreshDatabase::class);

it('keeps legacy client morph type so existing scope grants still resolve', function () {
    $owner = User::factory()->create();

    $client = Client::forceCreate([
        'id' => (string) Str::uuid(),
        'name' => 'Scoped Client',
        'secret' => hash('sha256', Str::random(40)),
        'redirect_uris' => ['http://localhost:8001/auth/callback'],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'revoked' => false,
        'owner_id' => $owner->id,
        'owner_type' => $owner->getMorphClass(),
        'access_policy' => ClientAccessPolicy::Open,
    ]);

    $resource = PassportScopeResource::create([
        'name' => 'profile',
        'description' => 'Profile resource',
        'is_active' => true,
    ]);

    $action = PassportScopeAction::create([
        'name' => 'read',
        'description' => 'Read action',
        'resource_id' => $resource->id,
        'is_active' => true,
    ]);

    PassportScopeGrant::create([
        'tokenable_type' => N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client::class,
        'tokenable_id' => $client->getKey(),
        'context_client_id' => $client->getKey(),
        'resource_id' => $resource->id,
        'action_id' => $action->id,
    ]);

    expect($client->getMorphClass())
        ->toBe(N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client::class);

    expect($client->hasScope('profile:read'))->toBeTrue();
});
