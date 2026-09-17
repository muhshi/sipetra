<?php

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('can render filament login page', function () {
    $this->get('/admin/login')
        ->assertSuccessful()
        ->assertSee('NIP / Email / Username / SOBAT ID');
});

test('can login to filament using email', function () {
    $user = User::factory()->create([
        'email' => 'admin@bps.go.id',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'admin@bps.go.id',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($user);
});

test('can login to filament using NIP', function () {
    $user = User::factory()->create([
        'nip' => '1987654321',
        'email' => 'pegawai@bps.go.id',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => '1987654321',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($user);
});

test('can login to filament using BPS username prefix', function () {
    $user = User::factory()->create([
        'email' => 'budi@bps.go.id',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'budi',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($user);
});

test('can login to filament using SOBAT ID', function () {
    $user = User::factory()->create([
        'sobat_id' => '332155443322',
        'email' => 'mitra_sobat@gmail.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => '332155443322',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($user);
});
