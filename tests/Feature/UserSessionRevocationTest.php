<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Gate::before(fn () => true);
    Role::create(['name' => 'super_admin']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('revokes all sessions and oauth tokens when admin clicks revoke_sessions', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $targetUser = User::factory()->create(['is_active' => true]);

    // Insert dummy session
    DB::table('sessions')->insert([
        'id' => Str::random(40),
        'user_id' => $targetUser->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'payload' => 'dummy-payload',
        'last_activity' => time(),
    ]);

    // Insert dummy oauth access token
    $tokenId = Str::random(40);
    DB::table('oauth_access_tokens')->insert([
        'id' => $tokenId,
        'user_id' => $targetUser->id,
        'client_id' => (string) Str::uuid(),
        'name' => 'Test Token',
        'scopes' => '[]',
        'revoked' => false,
        'created_at' => now(),
        'updated_at' => now(),
        'expires_at' => now()->addHour(),
    ]);

    // Insert dummy oauth refresh token
    DB::table('oauth_refresh_tokens')->insert([
        'id' => Str::random(40),
        'access_token_id' => $tokenId,
        'revoked' => false,
        'expires_at' => now()->addDays(30),
    ]);

    $this->actingAs($admin);

    livewire(ListUsers::class)
        ->callTableAction('revoke_sessions', $targetUser)
        ->assertNotified();

    // Verify sessions deleted
    expect(DB::table('sessions')->where('user_id', $targetUser->id)->count())->toBe(0);

    // Verify token revoked
    $token = DB::table('oauth_access_tokens')->where('id', $tokenId)->first();
    expect($token->revoked)->toBeTruthy();

    // Verify refresh token revoked
    $refreshToken = DB::table('oauth_refresh_tokens')->where('access_token_id', $tokenId)->first();
    expect($refreshToken->revoked)->toBeTruthy();
});
