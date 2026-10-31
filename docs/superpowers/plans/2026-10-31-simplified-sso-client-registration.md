# Simplified SSO Client Registration & Portal App Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyederhanakan alur pendaftaran aplikasi SSO baru menjadi form intuitif satu halaman dengan smart defaults (auto-callback, preset access policy, 1-click sync ke Portal Landing Page), serta mempercantik tampilan halaman detail (`ViewClient`) dan tabel dengan credential generator dan snippet `.env` siap salin.

**Architecture:** Mengganti wizard 3-langkah pada `CreateClient` dengan skema form terpadu `SimplifiedClientForm` yang otomatis mengisi konfigurasi teknis OAuth2 (grant type, actor as owner, default scopes, automatic callback URI), mengintegrasikan opsi pembuatan entri `PortalApp` dalam satu transaksi penyimpanan, dan meng-override `ViewClient` serta tabel `ClientResource` dengan tampilan card kredensial modern berformat monospace dan tombol 1-klik salin clipboard.

**Tech Stack:** Laravel 13, Filament v5, Livewire v4, Laravel Passport v13, Tailwind CSS.

**Spec:** Kebutuhan penyederhanaan alur pendaftaran SSO client dan integrasi portal aplikasi sesuai diskusi pengguna.

## Global Constraints
- Jangan menghapus, truncate, atau merusak data pada database (User Rule).
- Selalu jalankan `vendor/bin/pint --dirty --format agent` setelah perubahan file PHP (Boost Rule).
- Semua perubahan harus lolos pengujian programatik (`php artisan test --compact`).
- Commit dan push git dengan pesan yang jelas, lalu update Changelog di `README.md` dengan tanggal hari ini.

## Review Focus
1. Registrasi client SSO baru dengan preset kebijakan akses (`open`, `pegawai`, `mitra`, atau `custom`) tersimpan dengan access rules yang sesuai.
2. Callback Redirect URI otomatis terisi secara reaktif dari URL aplikasi tanpa memaksa admin mengetik manual `/auth/callback`.
3. Toggle "Tampilkan di Portal Landing Page" berhasil membuat/menyinkronkan entri `PortalApp` lengkap dengan nama, url, dan logo/icon.
4. Nilai `plain_secret` tersimpan di database `oauth_clients.plain_secret` sehingga kredensial Client ID & Client Secret tetap dapat dilihat dan disalin kapan saja pada halaman `ViewClient`.
5. Tombol "Salin Konfigurasi .ENV" menghasilkan format string `.env` yang valid (`SIPETRA_CLIENT_ID`, `SIPETRA_CLIENT_SECRET`, `SIPETRA_REDIRECT_URI`, `SIPETRA_BASE_URL`).

---

### Task 1: Create Simplified Client Form Schema (`SimplifiedClientForm`)

**Files:**
- Create: `app/Filament/Passport/Schemas/SimplifiedClientForm.php`
- Modify: `app/Filament/Resources/Passport/Pages/CreateClient.php`

**Interfaces:**
- Consumes: `ClientAccessPolicy` enum, `AccessRuleType` enum, `Filament\Schemas\Schema`, `Filament\Forms\Components\*`
- Produces: Clean unified single-page form schema replacing the 3-step wizard for client creation.

- [ ] **Step 1: Write test in `tests/Feature/ClientRegistrationTest.php` to verify client creation with simplified data payload and portal app sync**
- [ ] **Step 2: Run test to verify failure before implementation**
  Run: `php artisan test --filter=ClientRegistrationTest`
- [ ] **Step 3: Implement `SimplifiedClientForm`**
  - Section 1: Informasi Aplikasi (Nama Aplikasi, URL Dashboard dengan reactive auto-fill redirect URI, Redirect URI).
  - Section 2: Kebijakan Akses Pengguna (Pilihan Preset: `open` [Semua Pengguna Aktif], `pegawai` [Khusus Pegawai], `mitra` [Khusus Mitra], `custom` [Kustom via Repeater Rules]).
  - Section 3: Integrasi Portal Landing Page (Toggle aktifkan di portal, deskripsi, kepanjangan nama, logo aplikasi).
  - Section 4: Pengaturan Teknis Lanjutan (Collapsible: scopes default, grant type default `authorization_code`).
- [ ] **Step 4: Update `app/Filament/Resources/Passport/Pages/CreateClient.php`**
  - Gunakan `SimplifiedClientForm::configure($schema)` sebagai form.
  - Tangani `handleRecordCreation(array $data)`:
    - Auto-inject `grant_type = 'authorization_code'`.
    - Auto-inject `owner = auth()->id()`.
    - Auto-inject standard default scopes jika tidak dipilih custom.
    - Simpan `plain_secret` permanen ke `oauth_clients.plain_secret`.
    - Simpan `access_policy` & generate `ClientAccessRule` sesuai preset (`pegawai` -> `IdentityType::Pegawai`, `mitra` -> `IdentityType::Mitra`).
    - Jika `add_to_portal` dicentang, buat entri `PortalApp` yang terhubung.
    - Redirect setelah pembuatan ke halaman `view`.
- [ ] **Step 5: Run test to verify it passes**
  Run: `php artisan test --filter=ClientRegistrationTest`

---

### Task 2: Beautify `ViewClient` Page with Monospace .ENV Generator & Quick Guide

**Files:**
- Modify: `app/Filament/Resources/Passport/Pages/ViewClient.php`
- Create: `resources/views/filament/passport/credential-guide.blade.php` (snippet panduan integrasi cepat)

**Interfaces:**
- Consumes: `$record->getKey()` (client ID), `$record->plain_secret`, `$record->redirect_uris`
- Produces: Modern view interface with copyable credentials, monospace `.env` code block, and quick integration instructions.

- [ ] **Step 1: Write test in `tests/Feature/ClientViewTest.php` verifying ViewClient provides credentials and env snippet**
- [ ] **Step 2: Run test to verify failure**
  Run: `php artisan test --filter=ClientViewTest`
- [ ] **Step 3: Create Blade view `resources/views/filament/passport/credential-guide.blade.php` with tabbed quick start code examples (Laravel Socialite, Generic OAuth2)**
- [ ] **Step 4: Implement enhanced `ViewClient`**
  - Header actions: Tombol `Salin Konfigurasi .ENV` (clipboard write), tombol `Kunjungi Aplikasi` (buka URL client di tab baru), Edit, dan Delete.
  - Schema Form:
    - Section "Kredensial SSO": Client ID (copyable), Client Secret (revealed/hidden copyable), Redirect URI (copyable), Access Policy Badge.
    - Section "Format File Konfigurasi (.env)": Textarea monospace dengan tombol salin.
    - Section "Panduan Integrasi": Custom view component memuat panduan integrasi singkat.
- [ ] **Step 5: Run test to verify it passes**
  Run: `php artisan test --filter=ClientViewTest`

---

### Task 3: Enhance `ListClients` Table with Badges & Row Actions

**Files:**
- Modify: `app/Filament/Resources/Passport/ClientResource.php`

**Interfaces:**
- Consumes: Table builder for `ClientResource`
- Produces: Enhanced client table with Access Policy badges, Portal status indicator, and quick "Copy .ENV" table action.

- [ ] **Step 1: Define `table(Table $table)` on `ClientResource.php`**
  - Tampilkan Kolom:
    - Nama Aplikasi (dengan URL link ke dashboard aplikasi jika ada).
    - Client ID (monospace, copyable, limit/format rapi).
    - Access Policy (badge warna: Open = sukses/hijau, Restricted = info/biru atau warning/kuning).
    - Portal Status (Icon indicator apakah aplikasi tampil di Landing Page).
    - Status Revoke/Aktif.
    - Tanggal Dibuat.
  - Tambahkan Actions pada baris:
    - Quick Action: `Copy .ENV` (langsung salin konfigurasi ke clipboard tanpa harus masuk ke detail record).
    - View Action & Edit Action.
- [ ] **Step 2: Run test suite to verify table configuration and existing tests**
  Run: `php artisan test --compact`

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
