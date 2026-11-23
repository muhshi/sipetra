<?php

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mitra nik command updates existing mitra profile data including bank details', function () {
    $mitra = User::factory()->create([
        'identity_type' => IdentityType::Mitra,
        'sobat_id' => '332123110009',
        'name' => 'Sukilan',
        'nik' => null,
        'phone' => null,
        'alamat' => null,
        'nama_bank' => null,
        'nomor_rekening' => null,
        'nama_pemilik_rekening' => null,
    ]);

    $this->artisan('app:update-mitra-nik --no-insert')
        ->assertSuccessful();

    $mitra->refresh();

    expect($mitra->nik)->toBe('3321112009650001')
        ->and($mitra->alamat)->not->toBeNull()
        ->and($mitra->phone)->not->toBeNull()
        ->and($mitra->nama_bank)->toBe('(002) BANK BRI')
        ->and($mitra->nomor_rekening)->toBe('001601030699505')
        ->and($mitra->nama_pemilik_rekening)->toBe('Sukilan');
});
