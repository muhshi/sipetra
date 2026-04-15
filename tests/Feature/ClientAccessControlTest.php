<?php

use App\Enums\AccessRuleType;
use App\Enums\ClientAccessPolicy;
use App\Models\ClientAccessRule;
use App\Models\Passport\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->pegawai()->create();

    $this->client = Client::forceCreate([
        'id' => (string) Str::uuid(),
        'name' => 'Restricted Client',
        'secret' => hash('sha256', Str::random(40)),
        'redirect_uris' => ['http://localhost:8001/auth/callback'],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'revoked' => false,
        'owner_id' => $this->user->id,
        'owner_type' => $this->user->getMorphClass(),
        'access_policy' => ClientAccessPolicy::Restricted,
    ]);
});

it('denies oauth authorize when client is restricted and user has no matching rule', function () {
    $this->actingAs($this->user)
        ->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://localhost:8001/auth/callback',
            'response_type' => 'code',
            'scope' => 'profile:read',
            'state' => 'restricted-test',
        ]))
        ->assertForbidden()
        ->assertSee('Aplikasi ini belum mengizinkan akun Anda.')
        ->assertSee($this->client->name);
});

it('allows oauth authorize when user matches an access rule', function () {
    ClientAccessRule::create([
        'client_id' => $this->client->getKey(),
        'rule_type' => AccessRuleType::IdentityType,
        'rule_value' => 'pegawai',
    ]);

    $this->actingAs($this->user)
        ->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://localhost:8001/auth/callback',
            'response_type' => 'code',
            'scope' => 'profile:read',
            'state' => 'allowed-test',
        ]))
        ->assertSuccessful()
        ->assertSee('auth_token');
});
