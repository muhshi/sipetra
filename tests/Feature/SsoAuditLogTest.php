<?php

use App\Models\SsoAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('records audit log on helper call', function () {
    $user = User::factory()->create();

    $log = SsoAuditLog::log(
        user: $user,
        clientId: 'test-client',
        event: 'login_success',
        description: 'Authorized test client',
        ipAddress: '192.168.1.100',
        userAgent: 'SIPETRA-Test-Agent'
    );

    expect($log)->toBeInstanceOf(SsoAuditLog::class)
        ->and($log->user_id)->toBe($user->id)
        ->and($log->client_id)->toBe('test-client')
        ->and($log->event)->toBe('login_success')
        ->and($log->ip_address)->toBe('192.168.1.100')
        ->and($log->user_agent)->toBe('SIPETRA-Test-Agent')
        ->and($log->description)->toBe('Authorized test client');

    expect(SsoAuditLog::where('user_id', $user->id)->where('event', 'login_success')->exists())->toBeTrue();
});

test('captures request IP and User Agent when request instance is passed', function () {
    $user = User::factory()->create();
    $request = Request::create('/oauth/authorize', 'GET', [], [], [], [
        'REMOTE_ADDR' => '10.0.0.50',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 TestBrowser',
    ]);

    $log = SsoAuditLog::log(
        user: $user,
        clientId: 'alfath-app',
        event: 'login_success',
        description: 'Auto login via SSO',
        request: $request
    );

    expect($log->ip_address)->toBe('10.0.0.50')
        ->and($log->user_agent)->toBe('Mozilla/5.0 TestBrowser');
});

test('logs audit entry on oauth logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('oauth.logout'))
        ->assertRedirect(route('login'));

    expect(SsoAuditLog::where('user_id', $user->id)->where('event', 'logout')->exists())->toBeTrue();
});
