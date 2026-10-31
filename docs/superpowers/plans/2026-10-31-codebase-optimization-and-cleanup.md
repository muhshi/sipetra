# Codebase Optimization & Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengoptimasi performa kueri database (mengeliminasi N+1 dan double fetching), memperbaiki bug pemetaan field profil kontak API, mencegah potensi redirect loop 403, serta membersihkan file legacy dan kode redundan di SIPETRA.

**Architecture:** Menerapkan eager loading pada Eloquent query di Filament resources, menyelaraskan pemetaan atribut user dengan skema database asli, mengalihkan fallback authorization error ke route publik beranda (`home`), dan menghapus kelas legacy/duplikat tanpa mengubah kontrak API maupun skema database.

**Tech Stack:** Laravel 13, Filament v5, Livewire v4, Pest v4, Laravel Passport v13.

**Spec:** Berdasarkan hasil audit observasi kode pada sesi sebelumnya.

## Global Constraints
- Jangan menghapus, truncate, atau merusak data pada database (User Rule).
- Selalu jalankan `vendor/bin/pint --dirty --format agent` setelah perubahan file PHP (Boost Rule).
- Semua perubahan harus lolos pengujian programatik (`php artisan test --compact`).
- Commit dan push git dengan pesan yang jelas, lalu update Changelog di `README.md` dengan tanggal hari ini.

## Review Focus
1. Scope kontak API mengembalikan `phone_number` yang tepat sesuai data di tabel `users`.
2. Halaman daftar `UserResource` dan `EmployeeProfileResource` memuat relasi tanpa query N+1.
3. User tanpa hak akses panel di `/admin*` diarahkan aman ke halaman portal (`home`), bukan loop ke `/dashboard` -> `/admin`.
4. Form pembuatan/pengeditan Client Passport di Filament tetap berfungsi normal setelah binding legacy dibersihkan.
5. Deployment via SSH ke `bpsdemak@10.133.21.24` di direktori `apps/sipetra` berjalan lancar.

---

### Task 1: Fix Contact Scope Attribute & Test in UserApiController

**Files:**
- Modify: `app/Http/Controllers/Api/UserApiController.php:47-50`
- Modify: `tests/Feature/UserProfileApiTest.php`

**Interfaces:**
- Consumes: `User::$phone`
- Produces: API response JSON with `phone_number` key containing user's phone number when `contact:read` scope is present.

- [ ] **Step 1: Update test in `tests/Feature/UserProfileApiTest.php` to assert `phone_number` when `contact:read` scope is granted**
- [ ] **Step 2: Run test and verify failure before fix**
  Run: `php artisan test --filter=UserProfileApiTest`
- [ ] **Step 3: Update `UserApiController.php` line 48 to use `$user->phone ?? null`**
- [ ] **Step 4: Run test to verify it passes**
  Run: `php artisan test --filter=UserProfileApiTest`

---

### Task 2: Prevent 403 Redirect Loop in Exception Handler & Login Fallback

**Files:**
- Modify: `bootstrap/app.php:35-41`
- Modify: `app/Livewire/Auth/Login.php:79-82`

**Interfaces:**
- Consumes: `route('home')`
- Produces: Safe redirection to portal home on 403 or non-panel user instead of cycling between `/dashboard` and `/admin`.

- [ ] **Step 1: Update `bootstrap/app.php` line 38 to redirect to `route('home')` instead of `route('dashboard')`**
- [ ] **Step 2: Update `Login.php` line 80 to redirect to `route('home')` when user cannot access panel**
- [ ] **Step 3: Verify existing tests still pass**
  Run: `php artisan test --compact`

---

### Task 3: Resolve N+1 Queries & Double Fetching

**Files:**
- Modify: `app/Filament/Resources/Users/UserResource.php`
- Modify: `app/Filament/Resources/EmployeeProfiles/EmployeeProfileResource.php`
- Modify: `app/Filament/Passport/Schemas/ExtendedClientResourceForm.php:53-56`

**Interfaces:**
- Consumes: Eloquent relations `User::employeeProfile` and `EmployeeProfile::user`
- Produces: Eager loaded queries on table lists and single client instance retrieval in client resource form.

- [ ] **Step 1: Add `getEloquentQuery()` in `UserResource.php` with `with('employeeProfile')`**
- [ ] **Step 2: Add `getEloquentQuery()` in `EmployeeProfileResource.php` with `with('user')`**
- [ ] **Step 3: Refactor lines 53-56 in `ExtendedClientResourceForm.php` to fetch `Client::find($clientId)` once**
- [ ] **Step 4: Run tests to ensure no regressions**
  Run: `php artisan test --compact`

---

### Task 4: Remove Redundant Code, Legacy Files & Dead Artifacts

**Files:**
- Modify: `app/Providers/AppServiceProvider.php` (remove duplicate `Passport::useClientModel` and legacy bindings)
- Modify: `app/Providers/Filament/AdminPanelProvider.php` (remove redundant `SystemSettingsPage::class`)
- Modify: `app/Filament/Passport/Schemas/ExtendedClientWizardForm.php` (remove dead `getRuleValueLabel` method)
- Modify: `app/Filament/Widgets/LatestUsers.php` & `app/Filament/Widgets/PortalAppsWidget.php` (remove unused imports)
- Delete: `app/Filament/Resources/ClientResource` (legacy folder)
- Delete: `app/Policies/PassportClientPolicy.php` (unused policy)
- Delete: `app/Http/Resources/UserProfileResource.php` (unused resource)
- Delete: `fix_admin.php` (scratch script)

- [ ] **Step 1: Clean up `app/Providers/AppServiceProvider.php`**
- [ ] **Step 2: Remove redundant `SystemSettingsPage::class` in `AdminPanelProvider.php`**
- [ ] **Step 3: Remove unused private method `getRuleValueLabel` in `ExtendedClientWizardForm.php`**
- [ ] **Step 4: Clean up unused imports in widgets `LatestUsers.php` and `PortalAppsWidget.php`**
- [ ] **Step 5: Safely remove `fix_admin.php`, `app/Http/Resources/UserProfileResource.php`, `app/Policies/PassportClientPolicy.php`, and `app/Filament/Resources/ClientResource`**
- [ ] **Step 6: Run test suite to verify everything compiles and passes**
  Run: `php artisan test --compact`

---

### Task 5: Code Formatting & Verification

- [ ] **Step 1: Run Pint code formatter**
  Run: `vendor/bin/pint --format agent`
- [ ] **Step 2: Run complete test suite**
  Run: `php artisan test --compact`

---

### Task 6: Commit, Update Changelog & Push

**Files:**
- Modify: `README.md` (Changelog section)

- [ ] **Step 1: Update `README.md` Changelog with today's date (2026-10-31) and list of optimizations**
- [ ] **Step 2: Stage all changes, commit with clear message, and git push to origin**

---

### Task 7: SSH Deployment to Server

- [ ] **Step 1: Connect via SSH to `bpsdemak@10.133.21.24`**
- [ ] **Step 2: Navigate to `apps/sipetra`**
- [ ] **Step 3: Pull latest changes (`git pull`)**
- [ ] **Step 4: Run optimization/clear caches (`php artisan optimize:clear` or similar)**
- [ ] **Step 5: Verify deployment status on server**
