<?php

use App\Models\PortalApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('check client health command pings active apps and caches status', function () {
    Http::fake([
        'https://alfath.example.com*' => Http::response('OK', 200),
        'https://broken.example.com*' => Http::response('Server Error', 500),
        'https://down.example.com*' => function () {
            throw new ConnectionException('Connection timed out');
        },
    ]);

    $onlineApp = PortalApp::create([
        'name' => 'Alfath',
        'url' => 'https://alfath.example.com',
        'is_active' => true,
    ]);

    $warningApp = PortalApp::create([
        'name' => 'BrokenApp',
        'url' => 'https://broken.example.com',
        'is_active' => true,
    ]);

    $offlineApp = PortalApp::create([
        'name' => 'DownApp',
        'url' => 'https://down.example.com',
        'is_active' => true,
    ]);

    $inactiveApp = PortalApp::create([
        'name' => 'InactiveApp',
        'url' => 'https://ignored.example.com',
        'is_active' => false,
    ]);

    $this->artisan('app:check-client-health')->assertSuccessful();

    $onlineData = Cache::get('app_health_'.$onlineApp->id);
    expect($onlineData)->not->toBeNull()
        ->and($onlineData['status'])->toBe('online');

    $warningData = Cache::get('app_health_'.$warningApp->id);
    expect($warningData)->not->toBeNull()
        ->and($warningData['status'])->toBe('warning');

    $offlineData = Cache::get('app_health_'.$offlineApp->id);
    expect($offlineData)->not->toBeNull()
        ->and($offlineData['status'])->toBe('offline');

    expect(Cache::has('app_health_'.$inactiveApp->id))->toBeFalse();
});
