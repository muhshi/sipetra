<?php

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('it updates password for all mitra to Mitra3321 without touching pegawai or admin', function () {
    // Buat akun mitra dengan password lama
    $mitra1 = User::create([
        'name' => 'Mitra Satu',
        'email' => 'mitra1@example.com',
        'password' => Hash::make('OldMitraPass1'),
        'identity_type' => IdentityType::Mitra,
        'kd_satker' => '3321',
        'is_active' => true,
    ]);

    $mitra2 = User::create([
        'name' => 'Mitra Dua',
        'email' => 'mitra2@example.com',
        'password' => Hash::make('OldMitraPass2'),
        'identity_type' => IdentityType::Mitra,
        'kd_satker' => '3321',
        'is_active' => true,
    ]);

    // Buat akun pegawai dengan password 3321
    $pegawai = User::create([
        'name' => 'Pegawai Satu',
        'email' => 'pegawai1@bps.go.id',
        'password' => Hash::make('3321'),
        'identity_type' => IdentityType::Pegawai,
        'nip' => '198501012010011001',
        'kd_satker' => '3321',
        'is_active' => true,
    ]);

    // Buat akun admin
    $admin = User::create([
        'name' => 'Admin Satu',
        'email' => 'admin@example.com',
        'password' => Hash::make('AdminSecretPass'),
        'identity_type' => IdentityType::Admin,
        'is_active' => true,
    ]);

    // Jalankan perintah reset password mitra
    $this->artisan('app:reset-mitra-passwords')
        ->expectsOutputToContain('Berhasil memperbarui password untuk 2 akun mitra.')
        ->assertSuccessful();

    // Verifikasi mitra sudah berubah ke Mitra3321
    $mitra1->refresh();
    $mitra2->refresh();
    expect(Hash::check('Mitra3321', $mitra1->password))->toBeTrue()
        ->and(Hash::check('OldMitraPass1', $mitra1->password))->toBeFalse()
        ->and(Hash::check('Mitra3321', $mitra2->password))->toBeTrue()
        ->and(Hash::check('OldMitraPass2', $mitra2->password))->toBeFalse();

    // Verifikasi pegawai dan admin TIDAK berubah
    $pegawai->refresh();
    $admin->refresh();
    expect(Hash::check('3321', $pegawai->password))->toBeTrue()
        ->and(Hash::check('Mitra3321', $pegawai->password))->toBeFalse()
        ->and(Hash::check('AdminSecretPass', $admin->password))->toBeTrue()
        ->and(Hash::check('Mitra3321', $admin->password))->toBeFalse();
});

test('it handles custom password option for mitra', function () {
    $mitra = User::create([
        'name' => 'Mitra Test',
        'email' => 'mitratest@example.com',
        'password' => Hash::make('OldPass'),
        'identity_type' => IdentityType::Mitra,
        'is_active' => true,
    ]);

    $this->artisan('app:reset-mitra-passwords', ['--password' => 'CustomMitraPass'])
        ->assertSuccessful();

    $mitra->refresh();
    expect(Hash::check('CustomMitraPass', $mitra->password))->toBeTrue();
});
