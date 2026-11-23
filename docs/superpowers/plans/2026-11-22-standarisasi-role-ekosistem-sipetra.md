# Standarisasi Role Ekosistem Sipetra & Integrasi Satelit (Filament Shield) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans (or superpowers:subagent-driven-development) to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menstandarisasi hierarki role di seluruh ekosistem BPS Demak dengan 2 role fondasi dari Sipetra (`pegawai` dan `mitra`), memperbarui kontrak API Sipetra (Master Data & SSO Profile) untuk menyertakan role standar, serta menyediakan cetak biru (blueprint) integrasi Filament Shield untuk aplikasi satelit (Survey, Dinamit, dll).

**Architecture:** Menerapkan arsitektur *Additive Multi-Role (Composite Roles)*. Sipetra bertindak sebagai Single Source of Truth (SSOT) yang menjamin setiap pengguna memiliki role dasar (`pegawai` atau `mitra`) berdasarkan `identity_type`. Aplikasi satelit menggunakan Filament Shield dengan role dasar tersebut sebagai default permission tier, lalu menambahkan role operasional spesifik aplikasi (seperti `pencacah`, `pengawas`, `verifikator`) secara aditif tanpa menimpa role dasar saat sinkronisasi/login SSO.

**Tech Stack:** Laravel 13, Filament v5, Filament Shield v4, Spatie Laravel Permission v6, Laravel Passport v13, Pest v4.

**Spec:** Diskusi standarisasi role antara Sipetra dan aplikasi-aplikasi satelit (Survey, Dinamit) dengan Filament Shield.

## Global Constraints

- Jangan menghapus, truncate, atau merusak data pada database (`RULE[user_global]`).
- Jalankan `vendor/bin/pint --dirty --format agent` setelah perubahan file PHP.
- Semua perubahan harus lolos pengujian programatik (`php artisan test --compact`).
- Commit dan push git dengan pesan yang jelas, lalu update Changelog di `README.md` dengan tanggal hari ini.

## Review Focus

1. Output `MasterUserResource` (`GET /api/master/users`) menyertakan field `default_role` ('pegawai' | 'mitra') dan `roles` (array string) yang valid dan konsisten.
2. Endpoint profil SSO `GET /api/user` selalu mengembalikan role dasar pengguna bahkan tanpa scope tambahan, sehingga client app langsung dapat menentukan role dasar saat login.
3. User lifecycle di Sipetra (Observer/Model Hook) otomatis meng-assign role Spatie `pegawai` atau `mitra` saat user dibuat atau diupdate `identity_type`-nya.
4. Perintah sinkronisasi role massal (`php artisan sipetra:sync-default-roles`) berjalan aman, idempoten, dan tidak menghapus role administratif yang sudah ada (`super_admin`, `admin`).
5. Panduan integrasi client menyajikan implementasi konkret Filament Shield: skema penamaan role, seeder permission default, dan logika callback login SSO yang melindungi role lokal tambahan.

---

### Task 1: Tambahkan Lifecycle Hook & Command Sync Default Role di Sipetra

**Files:**
- Create: `app/Console/Commands/SyncDefaultRolesCommand.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/SyncDefaultRolesTest.php`

**Interfaces:**
- Consumes: `App\Models\User`, `App\Enums\IdentityType`, `Spatie\Permission\Models\Role`
- Produces: Otomatisasi assign role `pegawai` atau `mitra` saat user tersimpan, dan CLI command untuk backfill user lama.

- [ ] **Step 1: Write the failing test in `tests/Feature/SyncDefaultRolesTest.php`**
  Menguji bahwa user baru dengan `IdentityType::Pegawai` otomatis memiliki role `pegawai`, user dengan `IdentityType::Mitra` otomatis memiliki role `mitra`, dan command `sipetra:sync-default-roles` meng-assign role yang hilang ke semua user tanpa merusak role `super_admin`.
- [ ] **Step 2: Run test to verify it fails**
  Run: `php artisan test --compact --filter=SyncDefaultRolesTest`
  Expected: FAIL
- [ ] **Step 3: Implement Lifecycle Hook in `app/Models/User.php` & Artisan Command `SyncDefaultRolesCommand.php`**
  - Di `User::booted()`: tambahkan hook `saved` yang memanggil `$user->assignDefaultIdentityRole()`.
  - Buat command `php artisan sipetra:sync-default-roles` untuk memproses seluruh user di DB yang belum punya role sesuai `identity_type`.
- [ ] **Step 4: Run test to verify it passes**
  Run: `php artisan test --compact --filter=SyncDefaultRolesTest`
  Expected: PASS
- [ ] **Step 5: Jalankan formatter Pint & Commit**
  ```bash
  vendor/bin/pint --dirty --format agent
  git add app/Models/User.php app/Console/Commands/SyncDefaultRolesCommand.php tests/Feature/SyncDefaultRolesTest.php
  git commit -m "feat: tambahkan lifecycle hook dan command sync default roles"
  ```

---

### Task 2: Perbarui Master User Resource & User API Controller untuk Standardisasi Role

**Files:**
- Modify: `app/Http/Resources/MasterUserResource.php:20-65`
- Modify: `app/Http/Controllers/Api/UserApiController.php:25-33`
- Test: `tests/Feature/MasterUserControllerTest.php`
- Test: `tests/Feature/UserApiEndpointTest.php` (atau test user API yang sudah ada)

**Interfaces:**
- Consumes: `User` model, Spatie `roles` relationship
- Produces: Field `default_role` dan `roles` di respons `GET /api/master/users` dan `GET /api/user`.

- [ ] **Step 1: Write failing assertions in `tests/Feature/MasterUserControllerTest.php`**
  Assert bahwa response item memuat `default_role` ('pegawai' / 'mitra') dan `roles` (array string `['pegawai']` atau `['mitra']`).
- [ ] **Step 2: Run test to verify failure**
  Run: `php artisan test --compact --filter=MasterUserControllerTest`
  Expected: FAIL
- [ ] **Step 3: Implement payload updates in `MasterUserResource.php` and `UserApiController.php`**
  - Di `MasterUserResource`:
    ```php
    'default_role' => $this->identity_type instanceof IdentityType ? $this->identity_type->value : (string) $this->identity_type,
    'roles' => $this->getRoleNames()->values()->all(),
    ```
  - Di `UserApiController.php`: sertakan `default_role` dan `system_roles` secara default di profil dasar agar aplikasi klien langsung mendapatkan info role saat login SSO.
- [ ] **Step 4: Run test to verify it passes**
  Run: `php artisan test --compact --filter=MasterUserControllerTest`
  Expected: PASS
- [ ] **Step 5: Format dengan Pint & Commit**
  ```bash
  vendor/bin/pint --dirty --format agent
  git add app/Http/Resources/MasterUserResource.php app/Http/Controllers/Api/UserApiController.php tests/Feature/MasterUserControllerTest.php
  git commit -m "feat: sertakan default_role dan roles pada master data API dan user API"
  ```

---

### Task 3: Sinkronisasi OpenAPI Spec & Dokumentasi Master Data API

**Files:**
- Modify: `docs/openapi.yaml`
- Modify: `docs/API_MASTER_USERS.md`

**Interfaces:**
- Consumes: Field `default_role` dan `roles` dari `MasterUserResource`
- Produces: Dokumentasi kontrak OpenAPI dan panduan integrasi yang sinkron.

- [ ] **Step 1: Update schema `MasterUser` di `docs/openapi.yaml`**
  Tambahkan property `default_role` (string enum: `[pegawai, mitra, admin]`) dan `roles` (array of strings).
- [ ] **Step 2: Update `docs/API_MASTER_USERS.md`**
  Perbarui contoh JSON response dan tambahkan sub-bab mengenai "Standar Role Bawaan Sipetra".
- [ ] **Step 3: Commit dokumentasi**
  ```bash
  git add docs/openapi.yaml docs/API_MASTER_USERS.md
  git commit -m "docs: perbarui spesifikasi openapi dan panduan master users terkait role"
  ```

---

### Task 4: Buat Cetak Biru (Blueprint) Standarisasi Role & Filament Shield untuk Satelit

**Files:**
- Create: `docs/STANDARISASI_ROLE_DAN_FILAMENT_SHIELD.md`

**Interfaces:**
- Consumes: Standar role ekosistem BPS Demak (`pegawai`, `mitra`, role operasional satelit).
- Produces: Panduan lengkap, konvensi penamaan role, role hierarchy, snippet seeder Filament Shield, dan snippet SSO Callback untuk developer aplikasi Survey dan Dinamit.

- [ ] **Step 1: Tulis panduan arsitektur di `docs/STANDARISASI_ROLE_DAN_FILAMENT_SHIELD.md`**
  - **Prinsip Dasar**:
    1. Single Source of Truth (SSOT): Identitas dan 2 role dasar (`pegawai`, `mitra`) mutlak dikendalikan Sipetra.
    2. Additive Multi-Role: Role tambahan di aplikasi satelit tidak boleh menghapus role dasar `pegawai`/`mitra`.
  - **Standar Penamaan Role (Naming Convention)**:
    - Huruf kecil, `snake_case`, bahasa Indonesia konsisten:
      - Core: `pegawai`, `mitra`.
      - Survey: `pencacah`, `pengawas`, `admin_survey`.
      - Dinamit: `operator`, `verifikator`, `admin_dinamit`.
  - **Panduan Setup Filament Shield di Aplikasi Satelit**:
    - Definisi permission dasar untuk `pegawai` (misal: view dashboard, input data milik sendiri).
    - Definisi permission dasar untuk `mitra` (misal: hanya akses modul pencacahan/tugas aktif).
    - Seeder contoh Filament Shield (`ShieldRoleSeeder.php`).
  - **Snippet Callback SSO Login (Safe Role Sync)**:
    - Contoh kode Socialite / OAuth2 callback di satelit:
      ```php
      $user = User::updateOrCreate(['sipetra_id' => $ssoUser['id']], [...]);
      // Sync role dasar tanpa menghapus role operasional lokal yang sudah ada
      $currentRoles = $user->getRoleNames()->reject(fn ($r) => in_array($r, ['pegawai', 'mitra']))->toArray();
      $user->syncRoles(array_merge([$ssoUser['default_role']], $currentRoles));
      ```
- [ ] **Step 2: Review dokumen untuk kelengkapan dan kejelasan implementasi**
- [ ] **Step 3: Commit panduan standarisasi**
  ```bash
  git add docs/STANDARISASI_ROLE_DAN_FILAMENT_SHIELD.md
  git commit -m "docs: tambahkan panduan standarisasi role dan blueprint filament shield untuk aplikasi satelit"
  ```

---

### Task 5: Jalankan Perintah Sinkronisasi Data Eksisting & Full Test Suite

**Files:**
- Test: Seluruh unit dan feature test suite
- Execute: `php artisan sipetra:sync-default-roles`

- [ ] **Step 1: Jalankan command sync default roles pada database lokal**
  Run: `php artisan sipetra:sync-default-roles`
  Verify: Seluruh user pegawai dan mitra di DB lokal ter-assign role `pegawai` dan `mitra`.
- [ ] **Step 2: Jalankan full test suite**
  Run: `php artisan test --compact`
  Verify: Semua 50+ test lolos tanpa error.
- [ ] **Step 3: Update `README.md` Changelog**
  Catat perubahan standarisasi role dan penambahan panduan arsitektur satelit.
- [ ] **Step 4: Git commit & push**
  ```bash
  git add README.md
  git commit -m "chore: perbarui changelog readme setelah standarisasi role ekosistem"
  git push
  ```
