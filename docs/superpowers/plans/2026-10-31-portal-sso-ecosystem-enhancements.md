# Portal & SSO Ecosystem Enhancements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Elevate SIPETRA SSO into a comprehensive, modern institutional portal featuring self-service user profile & password management, user active session control, portal command palette (Ctrl+K), category filters, client application health monitoring, and centralized SSO audit logging.

**Architecture:** 
1. **Self-Service & Profile Management:** Built with Livewire v4 and integrated with Filament panels & Portal navbar, allowing users to view identity metadata, change password securely, and terminate their own active browser sessions.
2. **SSO Audit Logging:** Database-backed audit logging (`sso_audit_logs` table) recorded asynchronously during OAuth authorize and single logout events, displayed through a Filament Resource with search, date filters, and export.
3. **Portal Experience (Command Palette & App Categorization):** Alpine.js modal launcher bound to `Ctrl+K` / `Cmd+K` for instant app switching, plus tag/category pills on the portal grid.
4. **Client App Health Monitoring:** A lightweight artisan command (`app:check-client-health`) and cached status badges (Online/Warning/Offline) displayed dynamically on Portal cards.

**Tech Stack:** Laravel 13, Filament v5, Livewire v4, Alpine.js, Tailwind CSS v4, Laravel Passport v13, Pest v4.

**Spec:** Documented in chat history and requirements discussion.

---

## Global Constraints

- Never drop, truncate, or delete existing tables or database records.
- Run migrations safely using `php artisan migrate --force`.
- Format all modified/created PHP files using `vendor/bin/pint --format agent`.
- Programmatically verify every task with Pest test suites (`php artisan test --compact`).
- Commit each milestone with a descriptive message and update `README.md` Changelog.
- Deploy to remote server `10.133.21.24` via `bash deploy.sh` and verify HTTP 200.

## Review Focus

1. **Password Change Security:** Ensure current password verification cannot be bypassed and password confirmation is strictly enforced.
2. **Session Revocation Isolation:** Ensure a user can only view and revoke their own sessions in the sessions table (`user_id = auth()->id()`), never other users' sessions.
3. **Health Check Resiliency:** Ensure pinging offline or slow external apps does not hang the portal page load (health checks must run out-of-band or via cache with strict 2-second timeout).
4. **Audit Log Performance:** Audit log inserts must not slow down OAuth redirects or throw exceptions if metadata is malformed.
5. **Command Palette Accessibility:** Keyboard navigation (Escape to close, Up/Down arrow selection, Enter to navigate) must work flawlessly on desktop and mobile.

---

### Task 1: SSO Audit Logging System

**Files:**
- Create: `database/migrations/2026_10_31_110000_create_sso_audit_logs_table.php`
- Create: `app/Models/SsoAuditLog.php`
- Modify: `app/Http/Controllers/Auth/OAuthAuthorizationController.php`
- Modify: `app/Http/Controllers/Auth/OAuthLogoutController.php`
- Create: `app/Filament/Resources/SsoAuditLogResource.php`
- Test: `tests/Feature/SsoAuditLogTest.php`

**Interfaces:**
- Produces: `SsoAuditLog::log(User $user, ?string $clientId, string $event, ?string $description = null)`
- Events: `login_success`, `login_denied`, `logout`

- [ ] **Step 1: Write the failing test for audit logging**

```php
test('records audit log on successful oauth authorization', function () {
    $user = User::factory()->create();
    SsoAuditLog::log($user, 'test-client', 'login_success', 'Authorized test client');
    expect(SsoAuditLog::where('user_id', $user->id)->where('event', 'login_success')->exists())->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=SsoAuditLogTest`
Expected: FAIL (class/table does not exist)

- [ ] **Step 3: Implement migration and `SsoAuditLog` model**

Create migration with fields: `id`, `user_id`, `client_id`, `event`, `ip_address`, `user_agent`, `description`, `created_at`.
Implement `SsoAuditLog::log()` helper method capturing request IP and User-Agent automatically.

- [ ] **Step 4: Integrate audit logging into `OAuthAuthorizationController` and `OAuthLogoutController`**

Call `SsoAuditLog::log()` on approval and single logout events.

- [ ] **Step 5: Create `SsoAuditLogResource` in Filament Admin**

Add read-only table with columns: `created_at`, `user.name`, `client_id`, `event` (colored badge), `ip_address`, `user_agent`.

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --compact --filter=SsoAuditLogTest`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add database/migrations app/Models/SsoAuditLog.php app/Http/Controllers app/Filament tests
git commit -m "feat: implement centralized sso audit logging and filament viewer"
```

---

### Task 2: Self-Service User Profile & Password Change

**Files:**
- Create: `app/Livewire/Account/UserProfileModal.php`
- Create: `resources/views/livewire/account/user-profile-modal.blade.php`
- Modify: `resources/views/livewire/portal-landing-page.blade.php`
- Test: `tests/Feature/UserProfileSelfServiceTest.php`

**Interfaces:**
- Component `UserProfileModal`:
  - `updatePassword()` validating `current_password`, `password`, `password_confirmation`.
  - Emits `password-updated` notification.

- [ ] **Step 1: Write failing test for password update**

```php
test('authenticated user can change password with valid current password', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);
    Livewire::actingAs($user)
        ->test(UserProfileModal::class)
        ->set('current_password', 'old-password')
        ->set('new_password', 'new-password-123')
        ->set('new_password_confirmation', 'new-password-123')
        ->call('updatePassword')
        ->assertHasNoErrors();
    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=UserProfileSelfServiceTest`
Expected: FAIL

- [ ] **Step 3: Implement `UserProfileModal` Livewire component and Blade view**

Support tabs: 
1. **Identitas Diri** (NIP, Nama, Email, SOBAT ID, Satker, Jabatan - read-only).
2. **Ganti Password** (Form input with validation and success banner).

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=UserProfileSelfServiceTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/Account resources/views/livewire/account tests
git commit -m "feat: add user self-service profile and password change modal"
```

---

### Task 3: User Active Sessions Manager ("Active Devices")

**Files:**
- Modify: `app/Livewire/Account/UserProfileModal.php`
- Modify: `resources/views/livewire/account/user-profile-modal.blade.php`
- Test: `tests/Feature/UserActiveSessionsTest.php`

**Interfaces:**
- Produces: `revokeSession(string $sessionId)` (deletes from `sessions` where `user_id = auth()->id()`)
- Produces: `revokeOtherSessions()` (deletes from `sessions` where `user_id = auth()->id() AND id != session()->getId()`)

- [ ] **Step 1: Write failing test for self session revocation**

```php
test('user can revoke a specific session of their own', function () {
    $user = User::factory()->create();
    $sessionId = Str::random(40);
    DB::table('sessions')->insert([
        'id' => $sessionId,
        'user_id' => $user->id,
        'ip_address' => '1.2.3.4',
        'user_agent' => 'Test Browser',
        'payload' => 'payload',
        'last_activity' => time(),
    ]);

    Livewire::actingAs($user)
        ->test(UserProfileModal::class)
        ->call('revokeSession', $sessionId)
        ->assertHasNoErrors();

    expect(DB::table('sessions')->where('id', $sessionId)->count())->toBe(0);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=UserActiveSessionsTest`
Expected: FAIL

- [ ] **Step 3: Implement session management methods and UI in `UserProfileModal`**

Add tab **Sesi & Perangkat Aktif** listing parsed user-agent icons (Desktop/Mobile), IP address, last activity time, current session badge, and "Putus Sesi" button.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=UserActiveSessionsTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/Account resources/views/livewire/account tests
git commit -m "feat: add active devices and personal session revocation manager"
```

---

### Task 4: Portal UI/UX Overhaul (Navbar Profile, Spotlight Launcher `Ctrl+K`, & Category Filter)

**Files:**
- Modify: `resources/views/livewire/portal-landing-page.blade.php`
- Modify: `app/Livewire/PortalLandingPage.php`
- Modify: `database/migrations/2026_10_31_120000_add_category_to_portal_apps_table.php`
- Modify: `app/Filament/Resources/PortalApps/Schemas/PortalAppForm.php`
- Test: `tests/Feature/PortalExperienceTest.php`

**Interfaces:**
- Portal Navbar: Shows authenticated user chip with avatar, name, identity type badge, Profile Modal trigger, Admin link, and Logout action.
- Spotlight Palette: Alpine.js component listening to `window:keydown.ctrl.k` / `window:keydown.cmd.k` showing high-speed autocomplete modal.
- Category Tabs: Filter apps by category dynamically.

- [ ] **Step 1: Write test for category filtering and portal app rendering**

```php
test('portal page renders categorized apps and responds to search', function () {
    PortalApp::factory()->create(['name' => 'Alfath', 'category' => 'Produksi', 'is_active' => true]);
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('Alfath');
});
```

- [ ] **Step 2: Implement category migration and Filament schema update**

Add `category` column to `portal_apps` with preset options: `Umum & Kepegawaian`, `Statistik Sosial`, `Produksi & Distribusi`, `Nerwilis & IPDS`.

- [ ] **Step 3: Integrate Spotlight Launcher (Ctrl+K) & Navbar User Menu in `portal-landing-page.blade.php`**

Add keyboard event listener, smooth modal backdrop, highlight on search query match, and quick navigation.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=PortalExperienceTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Livewire resources/views/livewire database/migrations app/Filament tests
git commit -m "feat: portal spotlight command palette, category filters, and user navbar menu"
```

---

### Task 5: Client Application Health Checker

**Files:**
- Create: `app/Console/Commands/CheckClientAppHealth.php`
- Modify: `app/Livewire/PortalLandingPage.php`
- Modify: `resources/views/livewire/portal-landing-page.blade.php`
- Test: `tests/Feature/ClientAppHealthTest.php`

**Interfaces:**
- Produces: `Cache::get('app_health_' . $app->id)` returning `['status' => 'online'|'warning'|'offline', 'latency_ms' => int, 'checked_at' => string]`
- Command: `php artisan app:check-client-health`

- [ ] **Step 1: Write test for health checker command**

```php
test('check client health command pings active apps and caches status', function () {
    Http::fake([
        'https://example.com*' => Http::response('OK', 200),
    ]);
    $app = PortalApp::factory()->create(['url' => 'https://example.com', 'is_active' => true]);
    $this->artisan('app:check-client-health')->assertSuccessful();
    $cached = Cache::get('app_health_' . $app->id);
    expect($cached['status'])->toBe('online');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=ClientAppHealthTest`
Expected: FAIL

- [ ] **Step 3: Implement `CheckClientAppHealth` command**

Use `Http::timeout(2)->connectTimeout(1)->get($app->url)` to determine status without blocking. Store result in Cache for 5 minutes.

- [ ] **Step 4: Render health status badges on Portal cards**

Display subtle green/amber/red indicator dot beside each app name on the portal card.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --compact --filter=ClientAppHealthTest`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands app/Livewire resources/views/livewire tests
git commit -m "feat: implement client app health monitoring and portal status indicators"
```

---

### Task 6: Final Verification, Pint Formatting, Changelog & Production Deployment

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Run Pint code formatter**

Run: `vendor/bin/pint --format agent`

- [ ] **Step 2: Run complete Pest test suite**

Run: `php artisan test --compact`
Expected: 100% tests pass.

- [ ] **Step 3: Update `README.md` Changelog**

Add `[2026-10-31]` summary of all new features.

- [ ] **Step 4: Git commit and push**

```bash
git add .
git commit -m "feat: complete portal & sso ecosystem enhancements"
git push origin main
```

- [ ] **Step 5: Deploy to server via SSH**

Run: `ssh -o BatchMode=yes bpsdemak@10.133.21.24 "cd apps/sipetra && bash deploy.sh"`
Verify: `curl -k -s -o /dev/null -w "%{http_code}" http://localhost:8100/` returns `200`.
