<?php

use App\Models\PortalApp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('portal landing page renders active apps with category filters', function () {
    PortalApp::create([
        'name' => 'Alfath',
        'full_name' => 'Aplikasi Layanan Keuangan',
        'url' => 'http://localhost:8005',
        'category' => 'Umum & Kepegawaian',
        'is_active' => true,
        'order' => 1,
    ]);

    PortalApp::create([
        'name' => 'Survey',
        'full_name' => 'Portal Survei BPS',
        'url' => 'http://localhost:8002',
        'category' => 'Statistik Sosial',
        'is_active' => true,
        'order' => 2,
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Alfath')
        ->assertSee('Survey')
        ->assertSee('Umum & Kepegawaian')
        ->assertSee('Statistik Sosial');
});

test('portal shows user profile action and logout button when authenticated', function () {
    $user = User::factory()->create([
        'name' => 'Ahmad Faiz',
        'email' => 'faiz@bpsdemak.com',
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertSuccessful()
        ->assertSee('Ahmad Faiz')
        ->assertSee('Profil Akun');
});
