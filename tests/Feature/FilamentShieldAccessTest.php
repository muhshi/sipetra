<?php

use App\Enums\IdentityType;
use App\Filament\Pages\ManagePortalSettings;
use App\Filament\Pages\SystemSettingsPage;
use App\Filament\Resources\Passport\ClientResource;
use App\Filament\Resources\Passport\TokenResource;
use App\Filament\Resources\PortalAppResource;
use App\Filament\Resources\SsoAuditLogResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\PassportClient;
use App\Models\PortalApp;
use App\Models\SsoAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'mitra', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
});

test('mitra user cannot access administrative filament resources and pages', function () {
    $mitraUser = User::factory()->create([
        'identity_type' => IdentityType::Mitra,
        'is_active' => true,
    ]);
    $mitraUser->assignRole('mitra');

    $this->actingAs($mitraUser);

    // 1. Policy & Authorization checks for models
    expect(Gate::allows('viewAny', SsoAuditLog::class))->toBeFalse()
        ->and(Gate::allows('viewAny', PortalApp::class))->toBeFalse()
        ->and(Gate::allows('viewAny', User::class))->toBeFalse()
        ->and(Gate::allows('viewAny', PassportClient::class))->toBeFalse();

    // 2. Resource canViewAny checks
    expect(ClientResource::canViewAny())->toBeFalse()
        ->and(TokenResource::canViewAny())->toBeFalse()
        ->and(SsoAuditLogResource::canViewAny())->toBeFalse()
        ->and(PortalAppResource::canViewAny())->toBeFalse()
        ->and(UserResource::canViewAny())->toBeFalse();

    // 3. Page canAccess checks
    expect(ManagePortalSettings::canAccess())->toBeFalse()
        ->and(SystemSettingsPage::canAccess())->toBeFalse();
});

test('super admin user can access administrative filament resources and pages', function () {
    $superAdmin = User::factory()->create([
        'identity_type' => IdentityType::Admin,
        'is_active' => true,
    ]);
    $superAdmin->assignRole('super_admin');

    $this->actingAs($superAdmin);

    // 1. Policy & Authorization checks for models
    expect(Gate::allows('viewAny', SsoAuditLog::class))->toBeTrue()
        ->and(Gate::allows('viewAny', PortalApp::class))->toBeTrue()
        ->and(Gate::allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::allows('viewAny', PassportClient::class))->toBeTrue();

    // 2. Resource canViewAny checks
    expect(ClientResource::canViewAny())->toBeTrue()
        ->and(TokenResource::canViewAny())->toBeTrue()
        ->and(SsoAuditLogResource::canViewAny())->toBeTrue()
        ->and(PortalAppResource::canViewAny())->toBeTrue()
        ->and(UserResource::canViewAny())->toBeTrue();

    // 3. Page canAccess checks
    expect(ManagePortalSettings::canAccess())->toBeTrue()
        ->and(SystemSettingsPage::canAccess())->toBeTrue();
});
