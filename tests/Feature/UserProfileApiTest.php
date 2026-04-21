<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

it('returns the aggregated user profile payload from /api/user/me', function () {
    $user = User::factory()->pegawai()->create([
        'name' => 'Pegawai Demo',
        'email' => 'pegawai@example.com',
        'jabatan' => 'Statistisi Ahli Pertama',
        'unit_kerja' => 'BPS Demak',
    ]);

    Passport::actingAs($user, ['profile:read']);

    $this->getJson('/api/user/me')
        ->assertSuccessful()
        ->assertJson([
            'id' => $user->id,
            'name' => 'Pegawai Demo',
            'email' => 'pegawai@example.com',
            'client_role' => null,
            'profile' => [
                'identity_type' => 'pegawai',
            ],
            'organization' => [
                'jabatan' => 'Statistisi Ahli Pertama',
                'unit_kerja' => 'BPS Demak',
            ],
        ]);
});
