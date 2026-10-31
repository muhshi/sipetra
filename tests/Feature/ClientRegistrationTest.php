<?php

use App\Enums\ClientAccessPolicy;
use App\Enums\IdentityType;
use App\Filament\Resources\Passport\Pages\CreateClient;
use App\Models\ClientAccessRule;
use App\Models\Passport\Client;
use App\Models\PortalApp;
use App\Models\User;
use Database\Seeders\PassportScopeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'super_admin']);
    $this->seed(PassportScopeSeeder::class);
});

it('creates a client with simplified form and sets plain secret and open access policy', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin);

    livewire(CreateClient::class)
        ->fillForm([
            'name' => 'Aplikasi Survei Kilat',
            'app_url' => 'https://survei.bpsdemak.id',
            'redirect_uri' => 'https://survei.bpsdemak.id/auth/callback',
            'access_preset' => 'open',
            'add_to_portal' => true,
            'portal_description' => 'Aplikasi survei cepat statistik daerah',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = Client::where('name', 'Aplikasi Survei Kilat')->first();
    expect($client)->not->toBeNull()
        ->and($client->access_policy)->toBe(ClientAccessPolicy::Open)
        ->and($client->plain_secret)->not->toBeNull()
        ->and($client->plain_secret)->not->toBeEmpty();

    $portalApp = PortalApp::where('name', 'Aplikasi Survei Kilat')->first();
    expect($portalApp)->not->toBeNull()
        ->and($portalApp->url)->toBe('https://survei.bpsdemak.id')
        ->and($portalApp->description)->toBe('Aplikasi survei cepat statistik daerah');
});

it('creates a client with pegawai access preset and creates identity_type rule', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin);

    livewire(CreateClient::class)
        ->fillForm([
            'name' => 'Aplikasi Khusus PNS',
            'app_url' => 'https://kinerja.bpsdemak.id',
            'redirect_uri' => 'https://kinerja.bpsdemak.id/auth/callback',
            'access_preset' => 'pegawai',
            'add_to_portal' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = Client::where('name', 'Aplikasi Khusus PNS')->first();
    expect($client)->not->toBeNull()
        ->and($client->access_policy)->toBe(ClientAccessPolicy::Restricted);

    $rule = ClientAccessRule::where('client_id', $client->getKey())->first();
    expect($rule)->not->toBeNull()
        ->and($rule->rule_type->value)->toBe('identity_type')
        ->and($rule->rule_value)->toBe(IdentityType::Pegawai->value);
});
