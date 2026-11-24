<?php

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('it imports missing mitra users with default password Mitra3321', function () {
    // Jalankan perintah import
    $this->artisan('app:import-additional-mitra')
        ->assertSuccessful();

    // Verifikasi user yang diimport
    $user = User::where('email', 'elisaananda93@gmail.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Ananda Elisa Putri')
        ->and($user->identity_type)->toBe(IdentityType::Mitra)
        ->and($user->kd_satker)->toBe('3321')
        ->and($user->is_active)->toBeTrue()
        ->and(Hash::check('Mitra3321', $user->password))->toBeTrue();

    // Verifikasi total 73 user terdaftar
    expect(User::where('identity_type', IdentityType::Mitra)->count())->toBe(73);
});

test('it does not overwrite existing users and is idempotent', function () {
    // Buat salah satu user terlebih dahulu dengan password berbeda
    User::create([
        'name' => 'Ananda Elisa Putri',
        'email' => 'elisaananda93@gmail.com',
        'password' => Hash::make('OldPassword123'),
        'identity_type' => IdentityType::Mitra,
        'kd_satker' => '3321',
        'is_active' => true,
    ]);

    // Jalankan perintah import
    $this->artisan('app:import-additional-mitra')
        ->expectsOutputToContain('Sudah ada: Ananda Elisa Putri')
        ->assertSuccessful();

    // Pastikan password lama tidak tertimpa
    $user = User::where('email', 'elisaananda93@gmail.com')->first();
    expect(Hash::check('OldPassword123', $user->password))->toBeTrue()
        ->and(Hash::check('Mitra3321', $user->password))->toBeFalse();

    // Jalankan kedua kali untuk memastikan idempotency
    $this->artisan('app:import-additional-mitra')
        ->expectsOutputToContain('Dibuat: 0, Sudah Ada: 73, Total: 73')
        ->assertSuccessful();
});

test('it supports custom default password via option', function () {
    $this->artisan('app:import-additional-mitra', ['--password' => 'CustomSecretPass'])
        ->assertSuccessful();

    $user = User::where('email', 'elisaananda93@gmail.com')->first();
    expect(Hash::check('CustomSecretPass', $user->password))->toBeTrue();
});
