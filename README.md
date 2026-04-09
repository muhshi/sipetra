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
