# SIPETRA SSO (Single Sign-On Server)

SIPETRA (Sistem Identitas Tunggal Pegawai dan Mitra) adalah server autentikasi terpusat untuk mengelola identitas digital Pegawai BPS dan Mitra Statistik. Proyek ini dibangun menggunakan Laravel 13 dan didesain untuk mendukung ekosistem SSO di lingkungan BPS.

## Fitur Utama

- **OAuth2 Server**: Implementasi server OAuth2 menggunakan Laravel Passport.
- **Identity Management**: Pengelolaan data identitas untuk dua entitas utama:
  - **Pegawai**: Data PNS/PPPK BPS (NIP, Nama, Jabatan, Satker).
  - **Mitra**: Data Mitra Statistik (SOBAT ID, Profil).
- **Data Import Tools**: Seeder otomatis untuk mengimpor ribuan data dari JSON (`pegawai.json`) dan Excel (`mitra.xlsx`).
- **Filament Admin Panel**: Dashboard administratif yang kuat untuk pengelolaan user, role, dan permission (Shield).

## Instalasi

1.  Clone repositori ini.
2.  Install dependensi:
    ```bash
    composer install
    npm install
    ```
3.  Konfigurasi `.env` (pastikan database sudah terpasang).
4.  Jalankan migrasi dan seeder awal:
    ```bash
    php artisan migrate
    php artisan db:seed --class=AdminUserSeeder
    php artisan db:seed --class=RoleAndPermissionSeeder
    ```

## Import Data Pegawai & Mitra

Untuk mengimpor data mentah dari file JSON atau Excel:

1.  Pastikan file `pegawai.json` dan file Excel Mitra berada di direktori root.
2.  Jalankan seeder khusus import:
    ```bash
    php artisan db:seed --class=ImportUsersSeeder
    ```
    *Catatan: Default password untuk user yang diimport adalah `3321`.*

## Lisensi

[MIT license](https://opensource.org/licenses/MIT).

---

## Changelog

### [2026-04-13]
#### Added
- **System Settings Page**: Halaman pengaturan sistem di Filament Admin untuk mengatur Session Lifetime secara dinamis via dropdown (60 menit, 2 jam, 1 hari, 1 minggu, 1 bulan, selamanya). Menggunakan `spatie/laravel-settings` sebagai backend-nya.
- **Alur Pembuatan OAuth Client yang Disederhanakan**: Form `Create Client` kini cukup meminta Nama Aplikasi dan Link Dashboard. `Redirect URI` otomatis terisi (`{dashboard_url}/auth/callback`) namun tetap bisa diedit secara manual.
- **Penyimpanan & Tampilan Client Secret Permanen**: Kolom `plain_secret` ditambahkan ke tabel `oauth_clients` (via migrasi baru). Client Secret kini tersimpan dan dapat dilihat kapan saja di halaman detail client.
- **Tombol "Copy .ENV"**: Tombol hijau di halaman detail client untuk menyalin seluruh konfigurasi sekaligus dalam format siap-tempel `.env`:
  ```
  SIPETRA_CLIENT_ID="..."
  SIPETRA_CLIENT_SECRET="..."
  SIPETRA_REDIRECT_URI="..."
  ```
- **Migrasi `activity_log`**: Menerbitkan dan menjalankan migrasi dari paket `spatie/laravel-activitylog` yang dibutuhkan oleh paket N3XT0R untuk merekam log perubahan OAuth Client.
- **Client Access Control**: Fitur kontrol akses per aplikasi klien. Admin dapat menetapkan Role spesifik per klien (`client_roles`) dan menugaskan user aktif ke klien dengan Role tertentu (`client_user_accesses`). Termasuk dukungan UI Relation Manager di form detail klien.
- **Halaman Penolakan Akses**: Menambahkan halaman khusus `oauth.rejected` yang memblokir proses SSO jika user tidak memiliki izin ke aplikasi bersangkutan.
- **API `client_role`**: Endpoint `/api/user` kini mengembalikan data `client_role` otomatis sesuai klien yang melakukan request.

#### Changed
- **Grant Type dibatasi ke Authorization Code saja**: Opsi grant type lain (Password, Client Credentials, Implicit, Device, Personal Access) dihapus dari daftar pilihan di konfigurasi `passport-authorization-core`.
- **Database Scopes dinonaktifkan**: Fitur UI Scopes dari N3XT0R dinonaktifkan (`use_database_scopes => false`) karena scope dikelola secara statis melalui `AppServiceProvider`.
- **Model `PassportClient`**: Basis class diubah dari `Laravel\Passport\Client` menjadi `N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client` untuk kompatibilitas penuh dengan paket N3XT0R FilamentPassportUi.
- **`AppServiceProvider`**: Ditambahkan IoC Container Binding untuk override halaman `CreateClient`, `EditClient`, dan `ViewClient` dari vendor dengan versi kustom. Modifikasi juga pada `Passport::authorizationView` untuk mengarahkan pengguna yang tidak diizinkan ke halaman penolakan akses.
- **Enforcement SSO**: Metode `skipsAuthorization` model `PassportClient` dimodifikasi agar mengecek tabel izin sebelum melanjutkan Single Sign-On.

#### Fixed
- ***TypeError* pada halaman View Client**: Disebabkan oleh ketidakcocokan tipe model `PassportClient`. Diperbaiki dengan mengubah inheritance model.
- **Copy .ENV menghasilkan `null`**: Disebabkan oleh `$hidden` pada model Eloquent Passport yang memblokir akses ke `plain_secret`. Diperbaiki dengan menggunakan raw DB query (`DB::table(...)`) langsung, membypass layer Eloquent.
- **Class Action Not Found**: Memperbaiki masalah namespace Filament v5 di Relation Manager dari `Filament\Tables\Actions\` menjadi `Filament\Actions\`.

---

### [2026-04-15]
#### Added
- **Rule-Based Access Control (Fase 2)**: Sistem kontrol akses berbasis aturan fleksibel menggantikan whitelist user statis.
  - Kolom `access_policy` pada `oauth_clients` (`restricted` | `open`) mengontrol behavior saat tidak ada rule yang cocok.
  - Tabel `client_access_rules` mendukung 3 tipe aturan: `user` (user spesifik), `sipetra_role` (role Spatie), dan `identity_type` (pegawai/mitra/admin).
  - Evaluasi OR: user diizinkan jika cocok dengan **salah satu** rule yang aktif.
- **`AccessRuleResolver` Service**: Engine evaluasi akses terpusat yang digunakan oleh `PassportClient::skipsAuthorization()` dan `User::clientRoleFor()`.
- **Override OAuth Authorization Flow**: `OAuthAuthorizationController` mengintervensi flow Passport untuk memblokir user yang tidak diizinkan sebelum consent page ditampilkan.
- **Halaman `auth/oauth-denied`**: Halaman penolakan akses yang menampilkan nama aplikasi, akun yang digunakan, dan tombol Logout/Kembali.
- **`AccessRulesRelationManager`**: UI Filament baru (menggantikan `ClientUserAccessesRelationManager`) dengan form dinamis berdasarkan `rule_type`.
- **Field `access_policy` di `CreateClient`**: Admin dapat memilih kebijakan akses saat membuat OAuth client baru.
- **API Endpoint `GET /api/user/me`**: Endpoint baru yang mengembalikan profil lengkap user (profile + identitas + organisasi) via `UserProfileResource`.
- **`MigrateClientUserAccessesToRulesSeeder`**: Seeder untuk memindahkan data lama `client_user_accesses` ke `client_access_rules` sebagai `rule_type = 'user'`.

#### Changed
- **`PassportClient`**: `skipsAuthorization()` kini mendelegasikan evaluasi ke `AccessRuleResolver`. Tambah cast `access_policy` ke enum `ClientAccessPolicy` dan relasi `accessRules()`.
- **`User::clientRoleFor()`**: Kini menggunakan `AccessRuleResolver::resolveClientRole()`.
- **`ViewClient`**: `ClientUserAccessesRelationManager` digantikan oleh `AccessRulesRelationManager`.

### [Unreleased]
#### Added
- `ImportUsersSeeder` untuk proses batch import data Pegawai dan Mitra.
- Dukungan import Pegawai dari `pegawai.json`.
- Dukungan import Mitra dari file Excel menggunakan `phpoffice/phpspreadsheet`.
- Kolom identitas baru pada tabel `users` (NIP, SOBAT ID, dsb).
- Integrasi Filament Shield dan Laravel Passport.

#### Changed
- Pembaruan `README.md` dengan detail proyek SIPETRA SSO.
- Optimasi kecepatan seeder dengan caching password hash.

### [2026-04-08 & Awal 04-09 (Restored dari `nana-work`)]
#### Added
- *Added custom landing page (`resources/views/welcome.blade.php`) with professional dark theme and CTA buttons.*
- *Created `PegawaiSeeder` and `MitraSeeder` to import user data from `pegawai.json` and `mitra.xlsx` using `OpenSpout`. Seeded the database successfully.*
- Menambahkan dokumen `arsitektur_portal_sso.md` yang berisi panduan konseptual *Golden Flow* integrasi SSO dengan Aplikasi Portal.
- Menambahkan dokumen `panduan_integrasi_klien.md` yang berisi langkah teknis konkret bagi developer untuk menyambungkan aplikasi Klien dengan Sipetra.

### [2026-04-09]
#### Merged
- Melakukan pull dan menyinkronkan branch `nana-work` dengan branch `main` (`git pull origin main -X theirs`). Mengadopsi struktur seeder gabungan `ImportUsersSeeder` dan modifikasi README dari main.
#### Changed
- Pembaruan changelog untuk mencatat pull terbaru.
#### Fixed
- Menyelesaikan *bug* `AuthorizationViewResponse is not instantiable` dengan membuat `App\Models\PassportClient` khusus (SSO Otomatis Bypass Prompt) sehingga mempercepat proses *login* antar sistem internal.
- Menyelesaikan *bug* `Route [login] not defined` dengan mengarahkan *guest/unauthenticated users* ke rute Filament Admin (`/admin/login`).
- Mengubah fungsi `canAccessPanel()` pada `User.php` agar SELURUH pengguna (termasuk Pegawai dan Mitra) dapat berpartisipasi dalam sesi masuk SSO tanpa perlu ditolak Filament.
#### Added
- Menambahkan dokumen `arsitektur_portal_sso.md` yang berisi panduan konseptual *Golden Flow* integrasi SSO dengan Aplikasi Portal.
