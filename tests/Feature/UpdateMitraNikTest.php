<?php

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mitra nik command updates existing mitra profile data', function () {
    $mitra = User::factory()->create([
        'identity_type' => IdentityType::Mitra,
        'sobat_id' => '332123110009',
        'name' => 'Sukilan',
        'nik' => null,
        'phone' => null,
        'alamat' => null,
    ]);

    $this->artisan('app:update-mitra-nik')
        ->assertSuccessful();

    $mitra->refresh();

    expect($mitra->nik)->toBe('3321112009650001')
        ->and($mitra->alamat)->not->toBeNull()
        ->and($mitra->phone)->not->toBeNull();
});
