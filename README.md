# SIPETRA SSO (Single Sign-On Server)

SIPETRA (Sistem Identitas Tunggal Pegawai dan Mitra) adalah server autentikasi terpusat untuk mengelola identitas digital Pegawai BPS dan Mitra Statistik. Proyek ini dibangun menggunakan Laravel 13 dan didesain untuk mendukung ekosistem SSO di lingkungan BPS.

## Fitur Utama

- **OAuth2 Server**: Implementasi server OAuth2 menggunakan Laravel Passport.
- **Identity Management**: Pengelolaan data identitas untuk dua entitas utama:
  - **Pegawai**: Data PNS/PPPK BPS (NIP, Nama, Jabatan, Satker).
  - **Mitra**: Data Mitra Statistik (SOBAT ID, Profil).
- **Data Import Tools**: Seeder otomatis untuk mengimpor ribuan data dari JSON (`pegawai.json`) dan Excel (`mitra.xlsx`).
- **Filament Admin Panel**: Dashboard administratif yang kuat untuk pengelolaan user, role, dan permission (Shield).
- **Ruflo Agent Framework**: Sistem koordinasi agen AI (Swarm) untuk otomatisasi pengembangan dan pemeliharaan kode berbasis AgentDB.

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

## Alur Pengembangan (Agent Workflow)

Proyek ini menggunakan **Ruflo** untuk koordinasi agen AI. Beberapa perintah yang tersedia:

```bash
# Inisialisasi ulang jika diperlukan
npx ruflo@latest init

# Cek kesehatan sistem agen
npx ruflo@latest doctor --fix

# Jalankan daemon background
npx ruflo@latest daemon start
```

---

## Changelog

### [2026-11-02]
#### Fixed
- **Fix CSS Berantakan pada Modal Profil & Spotlight Portal**: Memperbaiki ketiadaan pemuatan asset Vite (`@vite(['resources/css/app.css', 'resources/js/app.js'])`) pada layout portal (`components/layouts/portal.blade.php`), menghapus dependensi ganda AlpineJS CDN yang bentrok dengan Livewire, serta menambahkan styling tangguh scoped CSS pada modal profil pengguna (`UserProfileModal`) agar modal tampil terpusat sempurna, berlatar belakang solid, form rapi, dan responsif di semua perangkat.

### [2026-10-31]
#### Added
- **Centralized SSO Audit Logging**: Sistem pencatatan riwayat audit SSO terpusat (`sso_audit_logs`) yang merekam aktivitas login berhasil, penolakan akses oleh access rule resolver, dan single logout (SLO). Dilengkapi viewer admin Filament `SsoAuditLogResource` dengan filter aktivitas, IP address copyable, pencarian pengguna/aplikasi, dan badge status visual.
- **Self-Service User Profile & Password Change**: Komponen Livewire modal `UserProfileModal` terpasang di portal utama, memungkinkan pengguna melihat profil identitas lengkap serta memperbarui password akun secara mandiri dengan validasi password lama yang aman. Mendukung route `/account` dan `/profile` (`?open_profile=1`) untuk integrasi link dari aplikasi client. Tampilan data dipisahkan secara otomatis: Pegawai hanya menampilkan NIP Baru, NIP Lama, Jabatan, Satker, dan No WA (ID SOBAT disembunyikan); sedangkan Mitra hanya menampilkan ID SOBAT, Periode Mitra, Masa Kontrak, dan No WA (NIP, Jabatan, dan Satker disembunyikan).
- **Active Devices & Session Revocation Manager**: Tab manajemen sesi aktif di modal profil pengguna yang menampilkan daftar browser, jenis perangkat (Desktop/Mobile), IP address, dan waktu aktivitas terakhir. Pengguna dapat mencabut sesi perangkat tertentu atau memutuskan seluruh sesi perangkat lain secara mandiri dengan isolasi akun yang ketat.
- **Portal Spotlight Command Palette (`Ctrl+K` / `Cmd+K`)**: Modal peluncur aplikasi super cepat berbasis Alpine.js dengan dukungan pintasan keyboard global `Ctrl+K`, pencarian instan pada nama, kepanjangan, dan deskripsi aplikasi, serta navigasi keyboard cepat.
- **Portal App Categorization & Filter Pills**: Penambahan kolom `category` pada tabel `portal_apps` (Umum & Kepegawaian, Statistik Sosial, Produksi & Distribusi, Nerwilis & IPDS) dengan filter pill interaktif pada katalog portal dan form selector terintegrasi pada Filament Admin `PortalAppResource`.
- **Portal Navbar User Chip**: Navbar portal kini menampilkan status user yang sedang login dengan avatar, nama lengkap, trigger modal profil, pintasan panel admin, serta tombol Single Logout (SLO) langsung.
- **Client Application Health Checker**: Perintah Artisan `app:check-client-health` untuk memeriksa ketersediaan (Online / Warning / Offline) dan latensi seluruh aplikasi klien via non-blocking HTTP ping dengan timeout aman (2s) yang disimpan di cache, dan divisualisasikan dengan lampu indikator status halus pada setiap kartu aplikasi portal.
- **Client Ecosystem Single Logout (SLO) & Anti Double-Click**: Menyempurnakan 9 aplikasi client di server (`alfath`, `aplikasi_penilaian`, `backend-demakai`, `magang`, `ManajemenSurat`, `microsite`, `puslah`, `SIPUTRI2.0`, `survey`) dengan `SsoLogoutResponse` untuk memutus sesi global SIPETRA secara otomatis saat logout dari client, serta mekanisme penguncian anti-double-click saat login SSO.
- **Simplified SSO Client Registration**: Mengganti wizard 3-langkah pendaftaran client OAuth dengan form terpadu 1-halaman (`SimplifiedClientForm`). Mendukung pengisian otomatis callback redirect URI dari URL aplikasi, pemilihan preset kebijakan akses instan (`open`, `pegawai`, `mitra`, atau `custom`), dan opsi 1-klik sinkronisasi langsung ke Portal Landing Page (`PortalApp`).
- **Beautified View Client & .ENV Snippet Generator**: Mempercantik halaman `ViewClient` dengan kartu kredensial modern, penampil Client Secret yang dapat di-reveal/copy kapan saja (disimpan permanen di `plain_secret`), textarea monospace `.env` siap salin, tombol 1-klik "Salin .ENV", tombol "Buka Aplikasi", serta panduan integrasi cepat framework Laravel.
- **Enhanced Client List Table**: Memperbarui tabel `ClientResource` dengan badge kebijakan akses visual, tautan URL, status aktif, serta quick action row "Salin .ENV" langsung dari tabel.
#### Optimized
- **Query N+1 Elimination**: Menambahkan eager loading `employeeProfile` pada `UserResource` dan `user` pada `EmployeeProfileResource` untuk mengeliminasi N+1 database query saat merender tabel di admin panel.
- **Double Fetch Optimization**: Mengurangi duplikasi query `Client::find()` pada saat merender schema perizinan scope di `ExtendedClientResourceForm`.

#### Fixed
- **SSO Login Double-Click / Race Condition Fix**: Memperbaiki masalah hilangnya `url.intended` saat tombol login diklik ganda ketika sedang loading. Intended OAuth URL kini di-cache di dalam Livewire component state sejak pertama kali dimount, tombol login otomatis dinonaktifkan (`wire:loading.attr="disabled"` dan Alpine submission guard) saat sedang memproses, dan pengecekan `Auth::check()` memastikan redirect selalu mengarah ke aplikasi klien tujuan alih-alih mental ke beranda/admin SIPETRA.
- **API Contact Scope Attribute**: Memperbaiki pemetaan nomor telepon di `UserApiController` dari sebelumnya `$user->no_hp` (undefined) menjadi `$user->phone` sesuai skema database, sehingga scope `contact:read` mengembalikan nomor telepon dengan benar.
- **Prevent 403 Redirect Loop**: Mengarahkan fallback error HTTP 403 di `bootstrap/app.php` dan fallback non-panel user di `Login.php` ke `route('home')` (bukan `route('dashboard')`) untuk mencegah infinite redirect loop antara `/dashboard` dan `/admin`.

#### Cleaned
- **Removal of Legacy ClientResource**: Menghapus direktori legacy `app/Filament/Resources/ClientResource` yang telah digantikan oleh `app/Filament/Resources/Passport/ClientResource`.
- **AppServiceProvider Cleanup**: Menghapus binding container lama untuk `CreateClient`/`EditClient`/`ViewClient` dan menghapus duplikasi pemanggilan `Passport::useClientModel(PassportClient::class)`.
- **Dead Code & Artifact Cleanup**: Menghapus `PassportClientPolicy.php` yang tidak terdaftar, `UserProfileResource.php` yang tidak terpakai, unused imports pada widget, unused method pada wizard form, registrasi manual redundan `SystemSettingsPage` di `AdminPanelProvider`, serta menghapus file skrip scratch `fix_admin.php`.
- **Code Styling**: Menjalankan Laravel Pint pada seluruh codebase yang dimodifikasi.

### [2026-05-25]
#### Changed
- **OAuth Authorization**: Memperbarui `OAuthAuthorizationController` dengan meng-override metode `hasGrantedScopes` untuk mengabaikan pengecekan kadaluarsa token (`expires_at`). Hal ini mencegah prompt persetujuan (consent screen) muncul berulang kali setiap kali token akses kadaluarsa, sehingga user hanya perlu menyetujui akses satu kali saja.

### [2026-05-24]
#### Added
- **SSO Login Redirect Loop (Race Condition)**: Memperbaiki login loop pada server produksi. Intended URL (`url.intended`) kini ditangkap **sebelum** regenerasi sesi untuk mencegah kehilangan data. Ditambahkan `session()->save()` sinkron dan logging diagnostik untuk membantu troubleshoot di environment FrankenPHP/Docker.

### [2026-05-23]
#### Added
- **Allow Admin Login via SSO**: Mengizinkan administrator untuk masuk menggunakan halaman login terpusat (SSO) utama, menghapus pembatasan sebelumnya.

#### Fixed
- **Null-Safe Client ID in UserApiController**: Menghindari TypeError saat penanganan token tanpa client ID (`actingAs` di pengujian unit).
- **Test Compatibility for Unified Login**: Menyesuaikan rute pengalihan pada Livewire Login component untuk mengarahkan pengguna ke `/dashboard` (menggunakan named route `dashboard`) sehingga kompatibel dengan assertion pengujian unit.
- **Outdated API Endpoint Tests**: Memperbarui pengujian pada `OAuthFlowTest` dan `UserProfileApiTest` yang sebelumnya memanggil endpoint lama (`/api/user/identity`, `/api/user/organization`, dan `/api/user/me`) agar menguji endpoint tunggal `/api/user` dengan scope dinamis baru.
- **RefreshDatabase in ExampleTest**: Menambahkan trait `RefreshDatabase` pada `ExampleTest` agar tabel `portal_apps` terbuat dengan benar ketika memanggil landing page root `/`.

### [2026-05-22]
#### Added
- **Unified Single-Field Login**: Menggabungkan input NIP dan Email ke dalam satu kolom input di halaman Login SSO SIPETRA, menghilangkan kebutuhan tab toggle NIP/Email untuk kemudahan akses pengguna.
- **BPS Email Prefix Support**: Memungkinkan pengguna dengan email BPS (`@bps.go.id`) untuk login hanya menggunakan prefix (awalan) email mereka tanpa perlu menuliskan domain lengkap.
- **Login Test Suite**: Menambahkan pengujian otomatis komprehensif (`tests/Feature/LoginTest.php`) menggunakan Pest untuk menguji semua skenario login (NIP lama, NIP baru, email lengkap, prefix email BPS, admin redirection, status tidak aktif, salah kredensial).

### [2026-05-06]
#### Added
- **Ruflo Agent Framework Integration**: Mengintegrasikan framework koordinasi agen **Ruflo V3 (Alpha 7)** ke dalam proyek. Ini mencakup inisialisasi engine **SwarmCoordinator**, setup **AgentDB** dengan indexing HNSW, dan konfigurasi **Background Workers** (Audit, Optimize, Map) untuk pemeliharaan codebase otomatis.
- **Merge Landing Page Branch**: Melakukan penggabungan branch `landingpage` ke `main` yang berisi fitur portal aplikasi.
- **Data Awal Aplikasi Portal**: Membuat `PortalAppsSeeder` untuk menginputkan otomatis 8 aplikasi klien bawaan (Alfath, Magang, CKP, Surat, Siputri, Demakai, Portal, Dinamit) ke dalam database beserta link URL-nya.
- **Portal Landing Page**: Implementasi *landing page* utama di URL root (`/`) menggunakan Livewire dan TailwindCSS. Halaman ini berfungsi sebagai portal kumpulan aplikasi milik klien.
- **Manajemen Portal (Filament)**: Menambahkan fitur `ManagePortalSettings` (menggunakan `spatie/laravel-settings`) untuk mengatur warna aksen dan teks *hero section*.
- **Manajemen Aplikasi (`PortalAppResource`)**: Menambahkan menu CRUD di Filament untuk mengelola daftar aplikasi (nama, deskripsi, URL, logo, status aktif, urutan) yang akan ditampilkan di *landing page*.

#### Changed
- **Dashboard Widgets**: Menambahkan widget statistik (`StatsOverview`), tabel pengguna terbaru (`LatestUsers`), dan daftar aplikasi portal (`PortalAppsWidget`) pada dashboard Filament untuk memantau jumlah Pegawai, Mitra, serta akses cepat ke aplikasi aktif secara *real-time*.
- **Redirection to /admin**: Mengubah semua titik masuk (landing page, login redirect, dan route `/dashboard`) untuk diarahkan ke `/admin` guna mendukung penggunaan Filament sebagai panel utama.

#### Fixed
- **Migration Idempotent Fix**: Memperbaiki error `duplicate column name: period` pada migration `add_mitra_contract_fields_to_users_table` dengan menambahkan pengecekan `hasColumn()`.
- **Cleanup**: Menghapus file sampah `first(['name'` hasil kesalahan eksekusi perintah di branch sebelumnya.

### [2026-05-03]
#### Fixed
- **Kompatibilitas Filament v5.6+ (Passport UI)**: Memperbaiki `FatalError` pada `ListClients` di mana `getHeaderActions()` harus bersifat `public` untuk menyesuaikan dengan deklarasi di parent class `N3XT0R\FilamentPassportUi`.
- **Generate Master Token Fix**: Memperbaiki `QueryException` "Unknown column 'personal_access_client'" saat generate token M2M. Pengecekan kini menggunakan `ClientRepository` bawaan Passport.
- **Robust Client Creation**: Mengganti pemanggilan `Artisan::call('passport:client')` dengan penggunaan `ClientRepository` secara langsung untuk membuat Personal Access Client. Hal ini menghindari error `CommandNotFoundException` di lingkungan server tertentu (Docker) yang mungkin mengalami kendala registrasi command console.
- **UI Master Token (Widget)**: Mengubah cara penampilan token hasil generate M2M. Token kini ditampilkan secara otomatis di dalam sebuah **Widget** dinamis di atas tabel `ListClients` sesaat setelah tombol konfirmasi ditekan. Widget ini dilengkapi tombol "Copy" khusus, lebih reliabel dibandingkan pendekatan Modal.

### [2026-04-29]
#### Added
- **Image Optimizer untuk Avatar**: Menambahkan paket `danihidayatx/image-optimizer` untuk mengkompresi gambar secara otomatis saat upload. Avatar di halaman Profil Pegawai (`EditProfile`) dan form User Admin (`UserForm`) kini dikonversi ke format WebP dengan kualitas 80% dan lebar maksimal 800px sebelum disimpan ke server. Batas upload diatur maksimal **10MB** untuk mencegah kegagalan pada server.

#### Changed
- **Callback URI — Repeater Component**: Mengganti field Textarea untuk Callback URI dengan komponen `Repeater`. Setiap URI kini memiliki baris input sendiri, menghilangkan kebutuhan pemisahan manual dengan koma dan meningkatkan pengalaman pengguna saat mengelola banyak URI (server vs lokal).
- **Callback URI — Placeholder & Format**: Placeholder diperbarui ke format `domain/auth/sipetra/callback` yang lebih relevan.

#### Fixed
- **Kompatibilitas Filament v5.6.0**: Memperbaiki `FatalError` pada `EmployeeProfileResource` yang disebabkan perubahan tipe properti `$navigationGroup` di Filament v5.6.0. Tipe diubah dari `?string` menjadi `string|UnitEnum|null` sesuai deklarasi tipe baru di parent class `Filament\Resources\Resource`.
- **Migration Idempotent**: Memperbaiki error `activity_log table already exists` dan `duplicate column name` saat `php artisan migrate` dijalankan. Ketiga migration Spatie ActivityLog (`create_activity_log_table`, `add_event_column`, `add_batch_uuid_column`) kini dilindungi dengan guard `hasTable()`/`hasColumn()` agar aman dijalankan berulang kali.
- **Callback URI Fix**: Memperbaiki error `[object Object]` pada field Callback URI dengan menggunakan `Simple Repeater`. Format data kini secara otomatis sinkron dengan array string di database tanpa perlu transformasi manual yang rentan error.

### [2026-04-24]
#### Added
- **OAuth Client Wizard**: Implementasi form pembuatan/pengeditan OAuth Client menggunakan Wizard 3-langkah yang lebih terorganisir.
- **Extended Client Schemas**: Pemisahan logika form client ke dalam `ExtendedClientResourceForm` dan `ExtendedClientWizardForm` untuk meningkatkan maintainability.

#### Changed
- **Refaktor Seeder**: Optimalisasi `PassportScopeSeeder` dan `ImportUsersSeeder` untuk penanganan scope yang lebih bersih dan efisien.
- **Cleanup Resource**: Pembersihan kode pada `ClientResource` dan `EditClient` dengan memindahkan logika form ke class schema terpisah.
- **Merge Update**: Melakukan pull dan merge dari branch `asmuam` untuk menyinkronkan fitur-fitur terbaru ke branch `main`.
- **Scope Restoration**: Mengembalikan scope `profile:read` secara formal untuk menjamin kompatibilitas dengan aplikasi klien yang masih memintanya, namun data profil dasar tetap dikirim secara otomatis tanpa pengecekan ketat di sisi API.

### [2026-04-23]
#### Fixed
- Perbaikan `BadMethodCallException` pada `ClientResource` saat membuat client baru dengan mendefinisikan model secara eksplisit.
- Penambahan **Bridge Model** `App\Models\PassportClient` untuk menangani referensi lama yang mungkin masih tertinggal di database atau cache.
- Mengubah pemanggilan `$record->accessRules()->create()` menjadi `\App\Models\ClientAccessRule::create()` di `CreateClient` untuk menghindari error pada server persisten (seperti FrankenPHP) yang masih menyimpan definisi class lama di memori.
- **Environment Restoration**: Menjalankan `composer install` untuk memperbaiki error `vendor/autoload.php` yang hilang dan memulihkan dependensi proyek.
- **Database Initialization**: Menjalankan migrasi database dan initial seeders (`RoleAndPermissionSeeder`, `AdminUserSeeder`, `PassportScopeSeeder`) untuk menyiapkan lingkungan pengembangan.
- **Centralized Login**: Mengubah redirect tamu default dari `/admin/login` ke `/login` agar alur SSO lebih konsisten melalui portal utama.
- **Admin Access**: Mengizinkan Administrator untuk login melalui portal utama `/login` tanpa paksaan untuk menggunakan halaman admin khusus.
- **Asset Build**: Menjalankan `npm run build` untuk menghasilkan manifest Vite yang hilang guna memperbaiki error `ViteManifestNotFoundException`.
- **New Roles**: Menambahkan role `pegawai` dan `mitra` ke dalam `RoleAndPermissionSeeder`.
- **Auto-Assign Roles**: Memperbarui `ImportUsersSeeder` dan `PegawaiSeeder` agar otomatis memberikan role `pegawai` atau `mitra` sesuai dengan `identity_type` pengguna saat proses import.
- **UI Access Control**: Membatasi tampilan tombol "Buka Dasbor Admin SIPETRA" di halaman dashboard agar hanya muncul bagi pengguna dengan role `super_admin`.

#### Changed
- Merged branch `asmuam` to `main` and resolved composer conflicts.
- Ran `composer install` to fix missing Filament plugins (Spatie Settings Plugin).
- Refactored Passport OAuth scopes to support granular data separation between Pegawai and Mitra.
- Updated `PassportScopeSeeder` with proper scope formatting (`identity_pegawai:read`, `employee:read`, etc).
- Refactored `UserApiController` to dynamically return data payload based on the requested token scopes.
- Simplified `routes/api.php` to use a single dynamic `/api/user` endpoint.

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

### [2026-10-31]
#### Added
- **Federated Single Logout (SLO)**: Endpoint `/oauth/logout` dan `/logout` yang mendukung query parameter `redirect_uri` (tervalidasi terhadap URL client OAuth terdaftar). Mengakhiri sesi SIPETRA, meregenerasi token CSRF, dan mencabut token OAuth aktif milik pengguna sehingga mencegah login otomatis tanpa kredensial saat pengguna logout dari aplikasi klien.
- **Admin Session Revocation (Putus Sesi)**: Aksi tabel pengguna di Filament Admin (`revoke_sessions`) yang memungkinkan administrator memutuskan seluruh sesi aktif pengguna di tabel `sessions` dan mencabut seluruh token OAuth (`oauth_access_tokens` dan `oauth_refresh_tokens`) secara instan.
- **Panduan Single Logout pada Klien**: Menambahkan dokumentasi dan contoh kode implementasi Single Logout di `credential-guide.blade.php` untuk aplikasi klien Laravel.

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
### [2026-09-17]
#### Changed
- Menyederhanakan formulir login SSO Livewire (`/login`) dengan menghapus tab terpisah NIP/Email dan menggantinya dengan satu input fleksibel (mendukung NIP, Email, Username, atau SOBAT ID).
- Menyesuaikan formulir login Filament Admin (`/admin/login`) agar mendukung input fleksibel (NIP, Email, Username, atau SOBAT ID) tanpa validasi email kaku.

### [2026-09-18]
#### Added
- Script deployment otomatis cerdas (`deploy.sh`) untuk lingkungan server:
  - Deteksi cerdas rebuild Docker: hanya rebuild image jika ada perubahan file Docker/Caddy atau container belum aktif.
  - Deteksi cerdas build frontend: hanya compile asset Vite (`npm run build`) jika ada perubahan file asset/views atau `public/build` belum tersedia.
  - Pengecekan update Composer: hanya menjalankan `composer install` jika `composer.lock` / `composer.json` mengalami perubahan.
  - Eksekusi migrasi aman (`migrate --force`), optimasi cache Laravel, reload FrankenPHP, pembersihan image lama, dan verifikasi status.
  - Opsi flag fleksibel: `--build` / `-b` (paksa Docker rebuild), `--build-assets` / `-a` (paksa Vite build), dan `--all`.
