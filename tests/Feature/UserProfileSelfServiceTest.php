<?php

use App\Livewire\Account\UserProfileModal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('authenticated user can change password with valid current password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password-123'),
    ]);

    $this->actingAs($user);

    livewire(UserProfileModal::class)
        ->set('current_password', 'old-password-123')
        ->set('new_password', 'new-secret-456')
        ->set('new_password_confirmation', 'new-secret-456')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertSet('passwordSuccessMessage', 'Password berhasil diperbarui.');

    expect(Hash::check('new-secret-456', $user->fresh()->password))->toBeTrue();
});

test('password change fails if current password is incorrect', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correct-password'),
    ]);

    $this->actingAs($user);

    livewire(UserProfileModal::class)
        ->set('current_password', 'wrong-password')
        ->set('new_password', 'new-secret-456')
        ->set('new_password_confirmation', 'new-secret-456')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);

    expect(Hash::check('correct-password', $user->fresh()->password))->toBeTrue();
});

test('user can view and revoke their own active sessions', function () {
    $user = User::factory()->create();
    $sessionId1 = Str::random(40);
    $sessionId2 = Str::random(40);

    DB::table('sessions')->insert([
        [
            'id' => $sessionId1,
            'user_id' => $user->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'payload' => 'payload1',
            'last_activity' => time() - 100,
        ],
        [
            'id' => $sessionId2,
            'user_id' => $user->id,
            'ip_address' => '10.0.0.2',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)',
            'payload' => 'payload2',
            'last_activity' => time() - 50,
        ],
    ]);

    // Insert session for another user to verify isolation
    $otherUser = User::factory()->create();
    $otherSessionId = Str::random(40);
    DB::table('sessions')->insert([
        'id' => $otherSessionId,
        'user_id' => $otherUser->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Linux; Android 13)',
        'payload' => 'payload3',
        'last_activity' => time(),
    ]);

    $this->actingAs($user);

    livewire(UserProfileModal::class)
        ->call('revokeSession', $sessionId1)
        ->assertHasNoErrors();

    // Session 1 revoked
    expect(DB::table('sessions')->where('id', $sessionId1)->count())->toBe(0);
    // Session 2 still exists
    expect(DB::table('sessions')->where('id', $sessionId2)->count())->toBe(1);
    // Other user's session remains untouched
    expect(DB::table('sessions')->where('id', $otherSessionId)->count())->toBe(1);
});

test('user cannot revoke sessions belonging to another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherSessionId = Str::random(40);

    DB::table('sessions')->insert([
        'id' => $otherSessionId,
        'user_id' => $otherUser->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'payload' => 'payload',
        'last_activity' => time(),
    ]);

    $this->actingAs($user);

    livewire(UserProfileModal::class)
        ->call('revokeSession', $otherSessionId);

    // Other user's session must not be deleted
    expect(DB::table('sessions')->where('id', $otherSessionId)->count())->toBe(1);
});

test('user can revoke all other active sessions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $currentSessionId = session()->getId();
    $otherSessionId = 'other-session-id-12345678901234567890';

    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $user->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
            'payload' => 'current',
            'last_activity' => time(),
        ],
        [
            'id' => $otherSessionId,
            'user_id' => $user->id,
            'ip_address' => '10.0.0.2',
            'user_agent' => 'Mozilla/5.0 (Android)',
            'payload' => 'other',
            'last_activity' => time() - 300,
        ],
    ]);

    livewire(UserProfileModal::class)
        ->call('revokeOtherSessions')
        ->assertHasNoErrors();

    expect(DB::table('sessions')->where('id', $otherSessionId)->count())->toBe(0);
});

test('pegawai profile displays nip and jabatan but hides sobat id', function () {
    $pegawai = User::factory()->create([
        'identity_type' => 'pegawai',
        'nip' => '1987654321',
        'nip_baru' => '198765432100000001',
        'jabatan' => 'Pranata Komputer Ahli Muda',
        'unit_kerja' => 'IPDS BPS Demak',
        'sobat_id' => 'SOBAT-HIDDEN-99',
    ]);

    $this->actingAs($pegawai);

    livewire(UserProfileModal::class)
        ->call('openModal', 'profile')
        ->assertSee('198765432100000001')
        ->assertSee('Pranata Komputer Ahli Muda')
        ->assertSee('IPDS BPS Demak')
        ->assertDontSee('SOBAT-HIDDEN-99');
});

test('mitra profile displays sobat id but hides nip and jabatan and satker', function () {
    $mitra = User::factory()->create([
        'identity_type' => 'mitra',
        'sobat_id' => 'SOBAT-3321-007',
        'nip' => 'NIP-HIDDEN-123',
        'nip_baru' => 'NIP-BARU-HIDDEN-456',
        'jabatan' => 'Jabatan Hidden',
        'unit_kerja' => 'Satker Hidden',
    ]);

    $this->actingAs($mitra);

    livewire(UserProfileModal::class)
        ->call('openModal', 'profile')
        ->assertSee('SOBAT-3321-007')
        ->assertDontSee('NIP-HIDDEN-123')
        ->assertDontSee('NIP-BARU-HIDDEN-456')
        ->assertDontSee('Jabatan Hidden')
        ->assertDontSee('Satker Hidden');
});

test('account and profile routes redirect authenticated users to portal with open_profile parameter', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/account')
        ->assertRedirect('/?open_profile=1');

    $this->actingAs($user)
        ->get('/profile')
        ->assertRedirect('/?open_profile=1');
});
