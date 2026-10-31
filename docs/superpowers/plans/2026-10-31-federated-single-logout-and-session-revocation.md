# Federated Single Logout & Session Revocation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyediakan endpoint Federated Single Logout (`GET /oauth/logout` dan `GET /logout`) dengan validasi URL client untuk mencegah sesi tertinggal saat logout dari aplikasi klien, menambahkan tombol admin untuk mencabut semua sesi & token OAuth pengguna secara paksa, serta memperbarui panduan integrasi logout di aplikasi klien.

**Architecture:** Membuat controller khusus `OAuthLogoutController` yang menangani terminasi sesi SIPETRA, mencabut token akses aktif, dan melakukan redirection aman hanya ke domain yang terdaftar di `oauth_clients`. Menambahkan action "Putus Semua Sesi" pada `UserResource` yang menghapus record di tabel `sessions` dan me-revoke token di `oauth_access_tokens` & `oauth_refresh_tokens`.

**Tech Stack:** Laravel 13, Laravel Passport v13, Filament v5, Livewire v4, Pest v4.

**Spec:** Kebutuhan Paket 1: Federated Single Logout dan Force Revocation Sesi.

## Global Constraints
- Jangan menghapus, truncate, atau merusak data pada database (User Rule).
- Selalu jalankan `vendor/bin/pint --dirty --format agent` setelah perubahan file PHP (Boost Rule).
- Semua perubahan harus lolos pengujian programatik (`php artisan test --compact`).
- Commit dan push git dengan pesan yang jelas, lalu update Changelog di `README.md` dengan tanggal hari ini.

## Review Focus
1. User yang mengakses `GET /oauth/logout` tanpa `redirect_uri` berhasil di-logout dan diarahkan ke halaman login SIPETRA.
2. User yang mengakses `GET /oauth/logout?redirect_uri=https://alfath.bpsdemak.id/login` berhasil di-logout dan diarahkan kembali ke URL aplikasi klien yang terdaftar di `oauth_clients`.
3. Upaya open-redirect dengan `redirect_uri` yang tidak terdaftar (contoh: `https://evil-site.com`) dicegah dan dialihkan aman ke halaman login SIPETRA.
4. Action "Putus Semua Sesi" di `UserResource` berhasil menghapus sesi di tabel `sessions` dan menandai `revoked = true` pada semua token OAuth pengguna tersebut.
5. Panduan integrasi di `ViewClient` memuat instruksi implementasi logout untuk aplikasi klien Laravel.

---

### Task 1: Federated Single Logout Endpoint & Tests

**Files:**
- Create: `app/Http/Controllers/Auth/OAuthLogoutController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/OAuthSingleLogoutTest.php`

**Interfaces:**
- Consumes: Query parameter `redirect_uri`
- Produces: HTTP redirect response after terminating session and revoking tokens

- [ ] **Step 1: Write tests in `tests/Feature/OAuthSingleLogoutTest.php`**
  - Test 1: Logout tanpa `redirect_uri` menghapus sesi dan redirect ke login.
  - Test 2: Logout dengan `redirect_uri` terdaftar me-redirect ke URL klien yang valid.
  - Test 3: Logout dengan `redirect_uri` tidak terdaftar dicegah (fallback ke login).
- [ ] **Step 2: Run test to verify failure before implementation**
  Run: `php artisan test --filter=OAuthSingleLogoutTest`
- [ ] **Step 3: Implement `app/Http/Controllers/Auth/OAuthLogoutController.php`**
  - Method `logout(Request $request)`:
    - Validasi `redirect_uri` terhadap kolom `redirect_uris` di tabel `oauth_clients`.
    - Logout dari auth guard web: `Auth::guard('web')->logout()`.
    - Revoke tokens user jika ada: update `oauth_access_tokens` set `revoked = true`.
    - Invalidate session dan regenerate CSRF token.
    - Redirect ke target URL yang tervalidasi atau fallback ke `route('login')`.
- [ ] **Step 4: Register routes in `routes/web.php`**
  - `Route::get('/oauth/logout', [OAuthLogoutController::class, 'logout'])->name('oauth.logout');`
  - `Route::match(['get', 'post'], '/logout', [OAuthLogoutController::class, 'logout'])->name('logout');`
- [ ] **Step 5: Run test to verify it passes**
  Run: `php artisan test --filter=OAuthSingleLogoutTest`

---

### Task 2: Force Revoke Sessions & OAuth Tokens in User Management

**Files:**
- Modify: `app/Filament/Resources/Users/UserResource.php`
- Modify: `app/Filament/Resources/Users/Tables/UsersTable.php`
- Create: `tests/Feature/UserSessionRevocationTest.php`

**Interfaces:**
- Consumes: User ID, tables `sessions`, `oauth_access_tokens`, `oauth_refresh_tokens`
- Produces: Action in Filament to revoke all active sessions and tokens for a given user

- [ ] **Step 1: Write test in `tests/Feature/UserSessionRevocationTest.php`**
  - Menguji pencabutan sesi dan penandaan `revoked = true` pada token oauth saat action dijalankan.
- [ ] **Step 2: Run test to verify failure**
  Run: `php artisan test --filter=UserSessionRevocationTest`
- [ ] **Step 3: Implement `revokeSessions` action in `UsersTable.php`**
  - Action button: Icon `heroicon-o-no-symbol`, color `danger`, label `Putus Semua Sesi`.
  - Modal confirmation: `requiresConfirmation()`.
  - Action handler:
    - `DB::table('sessions')->where('user_id', $record->id)->delete();`
    - `$tokenIds = DB::table('oauth_access_tokens')->where('user_id', $record->id)->pluck('id');`
    - `DB::table('oauth_access_tokens')->whereIn('id', $tokenIds)->update(['revoked' => true]);`
    - `DB::table('oauth_refresh_tokens')->whereIn('access_token_id', $tokenIds)->update(['revoked' => true]);`
    - Notification sukses ke admin.
- [ ] **Step 4: Run test to verify it passes**
  Run: `php artisan test --filter=UserSessionRevocationTest`

---

### Task 3: Update Integration Guide in `credential-guide.blade.php`

**Files:**
- Modify: `resources/views/filament/passport/credential-guide.blade.php`

- [ ] **Step 1: Add "Single Logout (SLO)" guide section with code snippet for Laravel client applications**
- [ ] **Step 2: Verify existing `ClientViewTest` passes**
  Run: `php artisan test --filter=ClientViewTest`

---

### Task 4: Code Formatting, Full Test Suite & Verification

- [ ] **Step 1: Run Pint code formatter**
  Run: `vendor/bin/pint --format agent`
- [ ] **Step 2: Run complete test suite**
  Run: `php artisan test --compact`

---

### Task 5: Commit, Update Changelog, Push & Deploy via SSH

**Files:**
- Modify: `README.md` (Changelog section)

- [ ] **Step 1: Update `README.md` Changelog with feature description and date `2026-10-31`**
- [ ] **Step 2: Stage all changes, commit, and git push to origin**
- [ ] **Step 3: Connect via SSH to `bpsdemak@10.133.21.24` and run `bash deploy.sh`**
- [ ] **Step 4: Verify remote deployment status**
