# SIPETRA — SSO Server BPS

## 1. Ringkasan Proyek

**SIPETRA** (Sistem Informasi Pegawai Terpadu) adalah **SSO (Single Sign-On) Server** berbasis OAuth2 untuk lingkungan BPS (Badan Pusat Statistik). Aplikasi ini menjadi **pusat autentikasi tunggal** bagi seluruh aplikasi sektoral BPS (client apps), sehingga pegawai dan mitra hanya perlu **satu akun** untuk mengakses semua sistem.

### Tech Stack

| Komponen         | Teknologi                        |
| ---------------- | -------------------------------- |
| Framework        | Laravel 13 (PHP 8.4)             |
| Admin Panel      | Filament v5                      |
| OAuth2 Server    | Laravel Passport 13.x            |
| Passport UI      | n3xt0r/filament-passport-ui 2.x  |
| Auth Core        | n3xt0r/laravel-passport-authorization-core 1.x |
| Role & Permission| Filament Shield + Spatie Permission |
| Frontend         | Livewire 4 + Tailwind CSS v4     |
| Database         | SQLite (dev) → MySQL/PostgreSQL (prod) |

### Apa itu SSO? (Penjelasan Sederhana)

```
┌─────────────────────────────────────────────────────────────┐
│                    TANPA SSO (Sekarang)                      │
│                                                              │
│  Pegawai harus login terpisah di setiap aplikasi:           │
│                                                              │
│  App Survei    → login dengan akun A                        │
│  App Keuangan  → login dengan akun B                        │
│  App Surat     → login dengan akun C                        │
│  ❌ Ribet, banyak password, sulit dikelola                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     DENGAN SSO (SIPETRA)                     │
│                                                              │
│  Pegawai login SEKALI di SIPETRA, lalu bisa akses semua:    │
│                                                              │
│  SIPETRA (SSO) ──→ App Survei    ✅ otomatis masuk          │
│                ──→ App Keuangan  ✅ otomatis masuk           │
│                ──→ App Surat     ✅ otomatis masuk           │
│  ✅ Satu akun, satu password, dikelola terpusat              │
└─────────────────────────────────────────────────────────────┘
```

### Bagaimana OAuth2 Bekerja? (Alur Authorization Code + PKCE)

```
Pegawai            App Client              SIPETRA (SSO Server)
  │                    │                          │
  │  1. Klik Login     │                          │
  │ ──────────────────>│                          │
  │                    │ 2. Redirect ke SIPETRA    │
  │                    │  /oauth/authorize         │
  │                    │ ─────────────────────────>│
  │                    │                          │
  │ 3. Tampil form login SIPETRA                  │
  │ <─────────────────────────────────────────────│
  │                    │                          │
  │ 4. Pegawai login + approve                    │
  │ ──────────────────────────────────────────────>│
  │                    │                          │
  │                    │ 5. Redirect balik + code  │
  │                    │ <─────────────────────────│
  │                    │                          │
  │                    │ 6. Tukar code → token     │
  │                    │   POST /oauth/token       │
  │                    │ ─────────────────────────>│
  │                    │                          │
  │                    │ 7. Access Token + Refresh │
  │                    │ <─────────────────────────│
  │                    │                          │
  │                    │ 8. GET /api/user          │
  │                    │   (pakai access token)    │
  │                    │ ─────────────────────────>│
  │                    │                          │
  │                    │ 9. Data profil user       │
  │                    │ <─────────────────────────│
  │                    │                          │
  │ 10. Berhasil login!│                          │
  │ <─────────────────│                          │
```

---

## 2. Status Implementasi

### ✅ Sudah Selesai
- [x] Laravel 13 (PHP 8.4 via Herd)
- [x] Filament v5 (panel `admin` di `/admin`)
- [x] Laravel Passport 13.x (`install:api --passport`, keys generated)
- [x] n3xt0r/filament-passport-ui 2.x (terinstall + plugin terdaftar)
- [x] n3xt0r/laravel-passport-authorization-core 1.x (migrations published)
- [x] Filament Shield (role & permission management)
- [x] Konfigurasi User model (`HasApiTokens`, `OAuthenticatable`, `FilamentUser`)
- [x] Migration: identity fields di users table
- [x] `api` guard dengan driver `passport` di `config/auth.php`
- [x] `IdentityType` enum (Pegawai, Mitra, Admin)
- [x] OAuth2 Scopes (6 scopes) di `AppServiceProvider`
- [x] Token lifetime config (access: 1h, refresh: 30d, personal: 6mo)
- [x] API endpoints (`/api/user`, `/api/user/identity`, `/api/user/organization`)
- [x] `UserApiController` dengan scope-based middleware
- [x] Filament `UserResource` (List/Create/Edit dengan conditional fields)
- [x] Seeders (`RoleAndPermissionSeeder`, `AdminUserSeeder`)
- [x] `UserFactory` dengan states (pegawai, mitra, inactive)
- [x] Roles: `super_admin`, `admin`, `operator`
- [x] Super admin user: `admin@sipetra.bps.go.id`

### ❌ Belum Dilakukan
- [ ] Custom halaman consent/authorize (Passport 13.x menggunakan JSON response)
- [ ] Testing (OAuth flow, API endpoints, Filament panel)
- [ ] Import data pegawai dari API BPS
- [ ] Import data mitra dari Excel SOBAT
- [ ] Dokumentasi client integration

---

## 3. Arsitektur Sistem

### 3.1 Database Schema — Users

```
┌──────────────────────────────────────┐
│              users                    │
├──────────────────────────────────────┤
│ id (bigint, auto)                    │
│ name (string)                        │
│ email (string, unique)               │
│ email_verified_at (timestamp, null)  │
│ password (string, hashed)            │
│ remember_token (string, null)        │
│ ─── Identitas ───                    │
│ identity_type (string, default:admin)│ → enum: pegawai, mitra, admin
│ nip (string, null, unique)           │ → NIP lama pegawai (9 digit)
│ nip_baru (string, null, unique)      │ → NIP baru pegawai (18 digit)
│ sobat_id (string, null, unique)      │ → SOBAT ID mitra (13 digit)
│ ─── Organisasi ───                   │
│ kd_satker (string(10), null)         │ → Kode satker (kdprop+kdkab)
│ jabatan (string, null)               │ → Nama jabatan
│ unit_kerja (string, null)            │ → Nama unit kerja / organisasi
│ golongan (string(10), null)          │ → Golongan (III/a, IV/b, dll)
│ ─── Data Pribadi ───                 │
│ jenis_kelamin (string(2), null)      │ → LK / PR
│ tempat_lahir (string, null)          │
│ tanggal_lahir (date, null)           │
│ pendidikan (string, null)            │
│ phone (string(20), null)             │
│ avatar_url (string, null)            │
│ ─── Status ───                       │
│ is_active (boolean, default:true)    │
│ timestamps                           │
├──────────────────────────────────────┤
│ INDEX: identity_type, kd_satker,     │
│        is_active                     │
│ UNIQUE: nip, nip_baru, sobat_id      │
└──────────────────────────────────────┘
```

### 3.2 Sumber Data User

#### Pegawai BPS (dari API Kepegawaian)

Data pegawai diambil dari API internal BPS. Contoh response:

```json
{
    "id": "340000388",
    "niplama": "340000388",
    "nipbaru": "                  ",
    "namagelar": "Moeljono Mardjan",
    "kdgol": "34",
    "nmgol": "III/d",
    "kdstjab": "1",
    "nmstjab": "Staf",
    "kdorg": "61710",
    "nmorg": "K.S Tk II (Tipe B)",
    "kdprop": "33",
    "kdkab": "21",
    "jk": "LK",
    "tgllahir": "01-01-1936",
    "tempatlhr": "Demak",
    "nmpend": "S.M.A Sastra",
    "nmwil": "Kab. Demak",
    "nmstpeg": "Pensiun",
    "nmjab": "Staf K.S Tk II (Tipe B)",
    "email": null
}
```

**Mapping ke users table:**

| API Field    | Users Column    | Keterangan                  |
| ------------ | --------------- | --------------------------- |
| `niplama`    | `nip`           | NIP lama (9 digit)          |
| `nipbaru`    | `nip_baru`      | NIP baru (18 digit)         |
| `namagelar`  | `name`          | Nama lengkap dengan gelar   |
| `nmgol`      | `golongan`      | Golongan (III/d, IV/a, dll) |
| `nmjab`      | `jabatan`       | Nama jabatan lengkap        |
| `nmorg`      | `unit_kerja`    | Nama organisasi             |
| `kdprop+kdkab` | `kd_satker`   | Kode satker (5 digit)       |
| `jk`         | `jenis_kelamin` | LK / PR                    |
| `tempatlhr`  | `tempat_lahir`  | Tempat lahir                |
| `tgllahir`   | `tanggal_lahir` | Tanggal lahir (dd-mm-yyyy)  |
| `nmpend`     | `pendidikan`    | Pendidikan terakhir         |
| `email`      | `email`         | Email (bisa null)           |
| —            | `identity_type` | Set `pegawai`               |

#### Mitra Statistik (dari Export SOBAT Excel)

Data mitra dari export Excel aplikasi SOBAT BPS. Kolom:

| Excel Column                   | Users Column    | Keterangan              |
| ------------------------------ | --------------- | ----------------------- |
| `Nama Lengkap`                 | `name`          | Nama mitra              |
| `SOBAT ID`                     | `sobat_id`      | ID unik mitra (13 digit)|
| `Email`                        | `email`         | Email mitra             |
| `No Telp`                      | `phone`         | Nomor telepon (+62...)  |
| `Alamat Prov` + `Alamat Kab`   | `kd_satker`     | Kode satker             |
| `Jenis Kelamin`                | `jenis_kelamin` | Lk→LK, Pr→PR           |
| `Tempat, Tanggal Lahir (Umur)*` | `tempat_lahir`, `tanggal_lahir` | Parse field |
| `Pendidikan`                   | `pendidikan`    | Pendidikan terakhir     |
| —                              | `identity_type` | Set `mitra`             |

### 3.3 User Identity Types

| Type       | Deskripsi               | Identifier | Sumber Data           |
| ---------- | ----------------------- | ---------- | --------------------- |
| `pegawai`  | Pegawai BPS (PNS/PPPK)  | NIP (lama/baru) | API Kepegawaian BPS |
| `mitra`    | Mitra Statistik          | SOBAT ID   | Export Excel SOBAT    |
| `admin`    | Administrator sistem     | Email      | Manual / Seeder       |

### 3.4 OAuth2 Scopes

| Scope               | Deskripsi                                        |
| ------------------- | ------------------------------------------------ |
| `profile:read`      | Baca nama, email, avatar (DEFAULT scope)         |
| `identity:read`     | Baca NIP, NIP Baru, SOBAT ID, tipe identitas, jenis kelamin, tempat/tanggal lahir, pendidikan |
| `organization:read` | Baca kd_satker, unit_kerja, jabatan, golongan    |
| `phone:read`        | Baca nomor telepon                               |
| `email:read`        | Baca alamat email                                |
| `user:manage`       | Akses penuh manajemen user (admin only)          |

### 3.5 API Endpoints

| Method | Endpoint               | Scope              | Response                                          |
| ------ | ---------------------- | ------------------ | ------------------------------------------------- |
| GET    | `/api/user`            | `profile:read`     | `{id, name, email, avatar}`                       |
| GET    | `/api/user/identity`   | `identity:read`    | `{identity_type, nip, nip_baru, sobat_id, jenis_kelamin, tempat_lahir, tanggal_lahir, pendidikan}` |
| GET    | `/api/user/organization` | `organization:read` | `{kd_satker, unit_kerja, jabatan, golongan}`     |

Semua endpoint menggunakan `auth:api` middleware + `CheckToken::using()` untuk scope enforcement.

### 3.6 Roles & Permissions

| Role          | Akses Panel | Permissions                          |
| ------------- | ----------- | ------------------------------------ |
| `super_admin` | ✅ Full      | Semua fitur tanpa batas              |
| `admin`       | ✅ Ya        | Kelola user, lihat roles             |
| `operator`    | ✅ Ya        | Kelola user (view + create + update) |

### 3.7 Filament Admin Panel

#### UserResource (`/admin/users`)
- **List**: Tabel dengan kolom nama, email, tipe identitas, NIP, SOBAT ID, satker, status aktif
- **Create**: Form dengan sections (Akun, Identitas, Organisasi, Data Pribadi)
  - Field identitas conditional: NIP muncul untuk Pegawai, SOBAT ID muncul untuk Mitra
  - Section organisasi muncul hanya untuk Pegawai dan Mitra
- **Edit**: Same as create, password optional
- **Filters**: Tipe identitas, status aktif

#### Passport UI (via n3xt0r/filament-passport-ui)
- **OAuth Clients**: Kelola client apps (create/edit/revoke)
- **Access Tokens**: Lihat dan revoke token
- **Scope Resources**: Kelola scope sebagai resource:action pairs
- **Scope Actions**: Kelola actions untuk tiap resource

---

## 4. Konfigurasi Penting

### 4.1 Auth Guard (`config/auth.php`)

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

### 4.2 Token Lifetime (`AppServiceProvider`)

```php
Passport::tokensExpireIn(now()->addHour());           // Access token: 1 jam
Passport::refreshTokensExpireIn(now()->addDays(30));  // Refresh token: 30 hari
Passport::personalAccessTokensExpireIn(now()->addMonths(6));
```

### 4.3 Passport Authorization Core

File: `config/passport-authorization-core.php`
- `cache.enabled` → `false` (SQLite/file cache tidak support tags, enable di production dengan Redis)
- `use_database_scopes` → `true`

### 4.4 User Model Traits & Interfaces

```php
class User extends Authenticatable implements FilamentUser, OAuthenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;
}
```

---

## 5. Belum Diimplementasi (Backlog)

### Fase Berikutnya: Import Data
- [ ] Artisan command untuk import pegawai dari API BPS
- [ ] Artisan command untuk import mitra dari Excel SOBAT
- [ ] Scheduled sync pegawai (cron job)

### Fase Berikutnya: Custom UI
- [ ] Custom halaman consent/authorize
- [ ] Branding BPS di halaman login Filament
- [ ] Custom login page dengan NIP/email toggle

### Fase Berikutnya: Testing
- [ ] Test OAuth authorization flow
- [ ] Test token exchange
- [ ] Test API scope enforcement
- [ ] Test Filament panel access by role
- [ ] Test UserResource CRUD

### Fase Berikutnya: Client Integration
- [ ] Dokumentasi untuk tim client app
- [ ] Contoh Socialite provider (`SipetraProvider`)
- [ ] Sample client app

---

## 6. Environment Variables

```env
# App
APP_NAME=SIPETRA
APP_URL=http://localhost:8000

# Cache (gunakan Redis di production untuk support cache tags)
CACHE_STORE=file

# Database (production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipetra
DB_USERNAME=root
DB_PASSWORD=

# Passport (auto-generated)
# Keys ada di storage/oauth-private.key dan storage/oauth-public.key
```

---

## 7. Keamanan

- **HTTPS wajib** di production
- **PKCE** digunakan untuk public clients (SPA/mobile)
- **Rate limiting** di semua endpoint OAuth
- **Token expiration** yang wajar (access: 1 jam, refresh: 30 hari)
- **Scope enforcement** — prinsip least privilege, menggunakan `CheckToken::using()`
- **CORS** dikonfigurasi hanya untuk domain client yang terdaftar
- **Audit log** via `spatie/laravel-activitylog` (dari passport-authorization-core)
- **Password hashing** menggunakan bcrypt (12 rounds)

---

## 8. Referensi Integrasi Client App

### Install Socialite di Client App
```bash
# Di aplikasi CLIENT (bukan SIPETRA)
composer require laravel/socialite
```

### Konfigurasi Custom Provider
```php
// config/services.php di CLIENT app
'sipetra' => [
    'client_id'     => env('SIPETRA_CLIENT_ID'),
    'client_secret' => env('SIPETRA_CLIENT_SECRET'),
    'redirect'      => env('SIPETRA_REDIRECT_URI'),
    'host'          => env('SIPETRA_HOST', 'https://sipetra.bps.go.id'),
],
```

### Custom Socialite Provider

```php
class SipetraProvider extends AbstractProvider
{
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            config('services.sipetra.host') . '/oauth/authorize',
            $state
        );
    }

    protected function getTokenUrl(): string
    {
        return config('services.sipetra.host') . '/oauth/token';
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            config('services.sipetra.host') . '/api/user',
            ['headers' => ['Authorization' => 'Bearer ' . $token]]
        );

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
        ]);
    }
}
```

---

## 9. Catatan Penting

> [!IMPORTANT]
> SIPETRA adalah **OAuth2 Server (Resource Owner)**. Dia TIDAK login ke aplikasi lain.
> Aplikasi lain (client apps) yang login KE SIPETRA untuk mendapat token.

> [!WARNING]
> Jangan pernah share `storage/oauth-private.key` ke client apps.
> Client apps hanya perlu `client_id`, `client_secret`, dan URL SIPETRA.

> [!TIP]
> Untuk development, bisa gunakan `php artisan passport:client` untuk membuat
> test client dengan redirect URI `http://localhost:8001/auth/callback`.

> [!NOTE]
> Cache scope dari passport-authorization-core dinonaktifkan (`cache.enabled = false`)
> karena SQLite/file cache tidak support cache tags. Aktifkan di production dengan Redis.