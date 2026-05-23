<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

it('returns the aggregated user profile payload from /api/user based on scopes', function () {
    $user = User::factory()->pegawai()->create([
        'name' => 'Pegawai Demo',
        'email' => 'pegawai@example.com',
        'jabatan' => 'Statistisi Ahli Pertama',
        'unit_kerja' => 'BPS Demak',
    ]);

    Passport::actingAs($user, ['profile:read', 'employee:read', 'roles:read']);

    $this->getJson('/api/user')
        ->assertSuccessful()
        ->assertJson([
            'id' => $user->id,
            'name' => 'Pegawai Demo',
            'email' => 'pegawai@example.com',
            'identity_type' => 'pegawai',
            'employee' => [
                'jabatan' => 'Statistisi Ahli Pertama',
                'unit_kerja' => 'BPS Demak',
            ],
            'system_roles' => [],
        ]);
});
