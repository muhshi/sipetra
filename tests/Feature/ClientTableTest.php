<?php

use App\Enums\ClientAccessPolicy;
use App\Filament\Resources\Passport\Pages\ListClients;
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

it('can list clients in the table with custom columns', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin);

    $client = Client::forceCreate([
        'id' => (string) Str::uuid(),
        'name' => 'Aplikasi Portal Test',
        'secret' => hash('sha256', 'plain-secret-test'),
        'plain_secret' => 'plain-secret-test',
        'redirect_uris' => ['https://portal.bpsdemak.id/auth/callback'],
        'grant_types' => ['authorization_code'],
        'revoked' => false,
        'owner_id' => $admin->id,
        'owner_type' => $admin->getMorphClass(),
        'access_policy' => ClientAccessPolicy::Open,
    ]);

    livewire(ListClients::class)
        ->assertCanSeeTableRecords([$client]);
});
