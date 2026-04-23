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
    php artisan db:seed --class=RoleAndPermissionSeeder
    php artisan db:seed --class=AdminUserSeeder
    php artisan db:seed --class=PassportScopeSeeder
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

### [2026-04-23]
#### Fixed
- Perbaikan `BadMethodCallException` pada `ClientResource` saat membuat client baru dengan mendefinisikan model secara eksplisit.
- Penambahan **Bridge Model** `App\Models\PassportClient` untuk menangani referensi lama yang mungkin masih tertinggal di database atau cache.
- Mengubah pemanggilan `$record->accessRules()->create()` menjadi `\App\Models\ClientAccessRule::create()` di `CreateClient` untuk menghindari error pada server persisten (seperti FrankenPHP) yang masih menyimpan definisi class lama di memori.

### [2026-04-22]
#### Added
- **Employee Profiles**: Menambahkan tabel `employee_profiles` untuk menyimpan data detil kepegawaian (TMT CPNS/PNS, TMT Golongan/Jabatan, Masa Kerja, Agama, dsb) guna merampingkan tabel `users`.
- **Self-Service Profile Page**: Implementasi halaman profil mandiri kustom (`EditProfile`) yang memungkinkan pegawai melihat data kepegawaian mereka (Read-Only) dan memperbarui data personal (Foto Profil, No HP, Tempat Lahir) secara mandiri.
- **Admin Sync Button**: Menambahkan tombol "Sync Data Pegawai" pada header `UserResource` untuk sinkronisasi massal dari `pegawai.json` serta kalkulasi otomatis masa kerja dan status keaktifan (Pensiun/Meninggal otomatis Inaktif).

#### Changed
- **User Management Resource**: Peningkatan `UserResource` dengan integrasi tab filter identitas, badge jumlah user, serta tampilan detil profil kepegawaian yang terintegrasi langsung dalam form edit user bagi Admin.

### [2026-04-21]
#### Changed
- **Pembersihan Pasca-Merge**: Melakukan pembersihan kode (cleanup) setelah penggabungan branch `asmuam`, termasuk perbaikan import class dan konsistensi gaya kode menggunakan Laravel Pint.
- **Optimasi Provider**: Merapikan `AppServiceProvider` dan `AdminPanelProvider` dengan menghapus import yang tidak digunakan dan menggunakan class alias yang lebih tepat.
- **Konfigurasi Docker**: Menambahkan `Dockerfile` (**PHP 8.4**), `docker-compose.yml` (Mapping port **8100**), integrasi network eksternal `mysql-stack_mysql_network`, dan `Caddyfile` (FrankenPHP).

### [2026-04-17]
#### Changed
- Inisialisasi repositori Git dan sinkronisasi dengan branch `asmuam` dari remote repository.

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
- **Client Access Control**: Fitur kontrol akses per aplikasi klien. Admin dapat menetapkan Role spesikan per klien (`client_roles`) dan menugaskan user aktif ke klien dengan Role tertentu (`client_user_accesses`). Termasuk dukungan UI Relation Manager di form detail klien.
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
