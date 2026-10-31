<?php

use App\Enums\ClientAccessPolicy;
use App\Filament\Resources\Passport\Pages\ViewClient;
use App\Models\Passport\Client;
use App\Models\User;
use Database\Seeders\PassportScopeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'super_admin']);
    $this->seed(PassportScopeSeeder::class);
});

it('renders the view client page with credentials and env config', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin);

    $plainSecret = 'super-secret-plain-token-12345';
    $client = Client::forceCreate([
        'id' => (string) Str::uuid(),
        'name' => 'Aplikasi Demo View',
        'secret' => hash('sha256', $plainSecret),
        'plain_secret' => $plainSecret,
        'redirect_uris' => ['https://demo.bpsdemak.id/auth/callback'],
        'grant_types' => ['authorization_code'],
        'revoked' => false,
        'owner_id' => $admin->id,
        'owner_type' => $admin->getMorphClass(),
        'access_policy' => ClientAccessPolicy::Open,
    ]);

    livewire(ViewClient::class, ['record' => $client->getKey()])
        ->assertSuccessful()
        ->assertFormSet([
            'id' => $client->getKey(),
            'name' => 'Aplikasi Demo View',
            'secret' => $plainSecret,
        ]);
});
