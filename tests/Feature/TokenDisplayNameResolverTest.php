<?php

use App\Models\User;
use App\Support\Passport\TokenDisplayNameResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

it('resolves token display name from the token owner instead of the client owner', function () {
    $clientOwner = User::factory()->create([
        'name' => 'Client Owner',
        'email' => 'owner@example.com',
    ]);

    $tokenOwner = User::factory()->pegawai()->create([
        'name' => 'Pegawai Login',
        'email' => 'pegawai@example.com',
    ]);

    $client = Client::forceCreate([
        'id' => (string) Str::uuid(),
        'name' => 'Dummy Client',
        'secret' => hash('sha256', 'secret'),
        'redirect_uris' => ['http://localhost/callback'],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'revoked' => false,
        'owner_id' => $clientOwner->id,
        'owner_type' => $clientOwner->getMorphClass(),
    ]);

    $token = Passport::token()->forceFill([
        'id' => Str::random(80),
        'user_id' => $tokenOwner->id,
        'client_id' => $client->getKey(),
        'name' => null,
        'scopes' => ['profile:read'],
        'revoked' => false,
        'expires_at' => now()->addHour(),
    ]);

    $token->save();
    $token->load('client');

    $resolvedName = app(TokenDisplayNameResolver::class)->execute($token);

    expect($resolvedName)->toBe('Pegawai Login')
        ->not->toBe('Client Owner');
});
