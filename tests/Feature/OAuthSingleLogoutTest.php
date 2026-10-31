<?php

use App\Models\Passport\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('logs out authenticated user without redirect_uri and redirects to login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/oauth/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('logs out and redirects to registered client url when redirect_uri matches client domain', function () {
    $user = User::factory()->create();

    Client::forceCreate([
        'id' => (string) Str::uuid(),
        'name' => 'Alfath App',
        'secret' => hash('sha256', 'secret'),
        'redirect_uris' => ['https://alfath.bpsdemak.id/auth/callback'],
        'grant_types' => ['authorization_code'],
        'revoked' => false,
    ]);

    $this->actingAs($user)
        ->get('/oauth/logout?redirect_uri=https://alfath.bpsdemak.id/login')
        ->assertRedirect('https://alfath.bpsdemak.id/login');

    $this->assertGuest();
});

it('prevents open redirect and redirects to login if redirect_uri is not registered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/oauth/logout?redirect_uri=https://evil-site.com/phishing')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
