<?php

use App\Enums\IdentityType;
use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('can render login page', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Single Sign-On')
        ->assertSee('Sistem Informasi Pegawai Terpadu (SIPETRA)');
});

test('can login using NIP lama', function () {
    $user = User::factory()->pegawai()->create([
        'nip' => '123456789',
        'password' => bcrypt('password'),
    ]);

    livewire(Login::class)
        ->fill([
            'username' => '123456789',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('can login using NIP baru', function () {
    $user = User::factory()->pegawai()->create([
        'nip_baru' => '123456789012345678',
        'password' => bcrypt('password'),
    ]);

    livewire(Login::class)
        ->fill([
            'username' => '123456789012345678',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('can login using full email', function () {
    $user = User::factory()->pegawai()->create([
        'email' => 'adib.test@bps.go.id',
        'password' => bcrypt('password'),
    ]);

    livewire(Login::class)
        ->fill([
            'username' => 'adib.test@bps.go.id',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('can login using BPS email prefix', function () {
    $user = User::factory()->pegawai()->create([
        'email' => 'adib@bps.go.id',
        'password' => bcrypt('password'),
    ]);

    livewire(Login::class)
        ->fill([
            'username' => 'adib',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('rejects login using normal email prefix (non-BPS)', function () {
    $user = User::factory()->mitra()->create([
        'email' => 'mitra123@gmail.com',
        'password' => bcrypt('password'),
    ]);

    livewire(Login::class)
        ->fill([
            'username' => 'mitra123',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasErrors(['username']);

    $this->assertGuest();
});

test('can login using full non-BPS email', function () {
    $user = User::factory()->mitra()->create([
        'email' => 'mitra123@gmail.com',
        'password' => bcrypt('password'),
    ]);

    livewire(Login::class)
        ->fill([
            'username' => 'mitra123@gmail.com',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('allows administrator login on SSO page', function () {
    $user = User::factory()->create([
        'email' => 'admin@bps.go.id',
        'password' => bcrypt('password'),
        'identity_type' => IdentityType::Admin,
    ]);

    livewire(Login::class)
        ->fill([
            'username' => 'admin',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('denies inactive user login', function () {
    $user = User::factory()->pegawai()->inactive()->create([
        'email' => 'inactive@bps.go.id',
        'password' => bcrypt('password'),
    ]);

    livewire(Login::class)
        ->fill([
            'username' => 'inactive',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasErrors(['username']);

    $this->assertGuest();
});
