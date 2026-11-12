<?php

use App\Enums\IdentityType;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('can render register page', function () {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Daftar Akun Baru')
        ->assertSee('SOBAT ID');
});

test('login page redirects to register when prompt=create or screen_hint=register', function () {
    $this->get(route('login', ['screen_hint' => 'register']))
        ->assertRedirect(route('register'));

    $this->get(route('login', ['prompt' => 'create']))
        ->assertRedirect(route('register'));
});

test('can register a new user as default mitra without sobat id', function () {
    Role::firstOrCreate(['name' => 'mitra', 'guard_name' => 'web']);

    livewire(Register::class)
        ->fill([
            'name' => 'Mitra Baru',
            'email' => 'mitra.baru@gmail.com',
            'phone' => '081234567890',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $user = User::where('email', 'mitra.baru@gmail.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Mitra Baru')
        ->and($user->identity_type)->toBe(IdentityType::Mitra)
        ->and($user->sobat_id)->toBeNull()
        ->and($user->hasRole('mitra'))->toBeTrue();
});

test('can register a new user with optional sobat id', function () {
    Role::firstOrCreate(['name' => 'mitra', 'guard_name' => 'web']);

    livewire(Register::class)
        ->fill([
            'name' => 'Mitra Sobat',
            'email' => 'mitra.sobat@gmail.com',
            'phone' => '089876543210',
            'sobat_id' => '3321998877',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->call('register')
        ->assertHasNoErrors();

    $user = User::where('email', 'mitra.sobat@gmail.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->sobat_id)->toBe('3321998877')
        ->and($user->identity_type)->toBe(IdentityType::Mitra);
});

test('validates duplicate email or sobat id on registration', function () {
    User::factory()->create([
        'email' => 'existing@gmail.com',
        'sobat_id' => '11223344',
    ]);

    livewire(Register::class)
        ->fill([
            'name' => 'User Duplikat',
            'email' => 'existing@gmail.com',
            'sobat_id' => '11223344',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->call('register')
        ->assertHasErrors(['email', 'sobat_id']);
});
