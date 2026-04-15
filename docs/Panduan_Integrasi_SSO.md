# Panduan Integrasi Aplikasi Klien dengan SIPETRA SSO Server

> **Versi**: 2.0 — 15 April 2026
> **Audiens**: Developer aplikasi sektoral / internal BPS yang ingin menggunakan SIPETRA sebagai penyedia identitas terpusat (SSO).

---

## Daftar Isi


1. [Gambaran Umum](#1-gambaran-umum)
2. [Prasyarat](#2-prasyarat)
3. [Mendapatkan Kredensial OAuth Client](#3-mendapatkan-kredensial-oauth-client)
4. [Integrasi Laravel (Socialite)](#4-integrasi-laravel-socialite)
5. [Integrasi Non-Laravel (Generic OAuth2)](#5-integrasi-non-laravel-generic-oauth2)
6. [Referensi API Endpoint SIPETRA](#6-referensi-api-endpoint-sipetra)
7. [Strategi Linking User Lokal](#7-strategi-linking-user-lokal)
8. [Penanganan Token & Refresh](#8-penanganan-token--refresh)
9. [Kebijakan Akses Klien (Access Policy)](#9-kebijakan-akses-klien-access-policy)
10. [Troubleshooting](#10-troubleshooting)
11. [Keamanan: Penanganan Browser Cache](#11-keamanan-penanganan-browser-cache)

---


## 1. Gambaran Umum

SIPETRA (Sistem Identitas Tunggal Pegawai dan Mitra) adalah server SSO berbasis **OAuth2 Authorization Code Grant** menggunakan Laravel Passport. Alur integrasi mengikuti diagram berikut:

```
┌──────────────┐        ┌──────────────┐        ┌──────────────┐
│  User        │        │  Klien App   │        │  SIPETRA SSO │
│  (Browser)   │        │  (Backend)   │        │  Server      │
└──────┬───────┘        └──────┬───────┘        └──────┬───────┘
       │ 1. Klik "Login SSO"   │                       │
       │ ─────────────────────>│                       │
       │                       │ 2. Redirect            │
       │ <─────────────────────│ /oauth/authorize       │
       │ 3. Browser redirect ──────────────────────────>│
       │                       │                       │
       │                       │     4. User login di   │
       │                       │     halaman SIPETRA    │
       │                       │                       │
       │ 5. Redirect kembali dengan ?code=xxx           │
       │ <──────────────────────────────────────────────│
       │ ─────────────────────>│                       │
       │                       │ 6. POST /oauth/token   │
       │                       │ (tukar code → token)  │
       │                       │ ──────────────────────>│
       │                       │ <──────────────────────│
       │                       │ 7. access_token       │
       │                       │                       │
       │                       │ 8. GET /api/user/me    │
       │                       │ ──────────────────────>│
       │                       │ <──────────────────────│
       │                       │ 9. Data profil user   │
       │                       │                       │
       │ 10. Login berhasil!   │                       │
       │ <─────────────────────│                       │
```

---

## 2. Prasyarat

### Di Sisi Server SIPETRA
- Server SIPETRA sudah berjalan dan dapat diakses oleh klien (misal: `https://sipetra.test`)
- Admin SIPETRA sudah membuat OAuth Client untuk aplikasi Anda
- Access Policy client sudah dikonfigurasi (`Open` atau `Restricted` dengan rules)

### Di Sisi Aplikasi Klien
- PHP 8.3+ (untuk integrasi Laravel)
- Tabel `users` lokal yang memiliki kolom untuk menyimpan data SSO (lihat [Bagian 7](#7-strategi-linking-user-lokal))

---

## 3. Mendapatkan Kredensial OAuth Client

### Langkah 1: Hubungi Admin SIPETRA

Admin SIPETRA akan membuat OAuth Client untuk aplikasi Anda melalui panel Filament Admin:
1. Isi **Nama Aplikasi** (contoh: "Portal BPS Demak")
2. Isi **Dashboard URL** aplikasi Anda (contoh: `https://portal.test`)
3. **Redirect URI** akan otomatis ter-generate: `https://portal.test/auth/sipetra/callback`
4. Admin akan menyalin konfigurasi `.env` untuk Anda

### Langkah 2: Simpan Kredensial di `.env`

Tambahkan konfigurasi berikut ke file `.env` aplikasi Anda:

```env
SIPETRA_CLIENT_ID="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
SIPETRA_CLIENT_SECRET="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
SIPETRA_REDIRECT_URI="https://sipetra-client.test/auth/sipetra/callback"
SIPETRA_BASE_URL="https://sipetra.test"
```

> ⚠️ **PENTING**: `SIPETRA_CLIENT_SECRET` bersifat rahasia. Jangan commit ke Git atau expose ke frontend.

---

## 4. Integrasi Laravel (Socialite)

### 4.1. Install Dependensi

```bash
composer require laravel/socialite
```

### 4.2. Tambahkan Konfigurasi Service

Buka `config/services.php` dan tambahkan:

```php
'sipetra' => [
    'client_id'     => env('SIPETRA_CLIENT_ID'),
    'client_secret' => env('SIPETRA_CLIENT_SECRET'),
    'redirect'      => env('SIPETRA_REDIRECT_URI'),
    'base_url'      => env('SIPETRA_BASE_URL', 'https://sipetra.test'),
    'scopes'        => ['profile:read', 'identity:read', 'organization:read'],
],
```

### 4.3. Buat Socialite Provider Kustom

Buat file `app/Providers/SipetraSocialiteProvider.php`:

```php
<?php

namespace App\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class SipetraSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            config('services.sipetra.base_url') . '/oauth/authorize',
            $state
        );
    }

    protected function getTokenUrl()
    {
        return config('services.sipetra.base_url') . '/oauth/token';
    }

    protected function getUserByToken($token)
    {
        // Menggunakan endpoint /api/user/me untuk mendapatkan profil lengkap
        $response = $this->getHttpClient()->get(
            config('services.sipetra.base_url') . '/api/user/me',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'     => $user['id'],
            'name'   => $user['name'],
            'email'  => $user['email'],
            'avatar' => $user['avatar'] ?? null,
        ]);
    }

    protected function getTokenFields($code)
    {
        $fields = parent::getTokenFields($code);
        $fields['grant_type'] = 'authorization_code';
        return $fields;
    }

    protected function getDefaultScopes()
    {
        return config('services.sipetra.scopes', ['profile:read']);
    }
}
```

> 💡 **TIP**: Provider ini menggunakan endpoint `/api/user/me` yang mengembalikan profil lengkap (identity + organization) dalam satu panggilan. Jika Anda hanya membutuhkan data dasar, ganti ke `/api/user`.

### 4.4. Daftarkan Provider di AppServiceProvider

Di `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\SipetraSocialiteProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Socialite::extend('sipetra', function ($app) {
            $config = $app['config']['services.sipetra'];
            return Socialite::buildProvider(SipetraSocialiteProvider::class, $config);
        });
    }
}
```

### 4.5. Buat SsoController

Buat file `app/Http/Controllers/Auth/SsoController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    /**
     * Redirect ke halaman login SIPETRA.
     */
    public function redirect()
    {
        return Socialite::driver('sipetra')->redirect();
    }

    /**
     * Handle callback dari SIPETRA setelah user login.
     */
    public function callback(Request $request)
    {
        // Tangani jika user menolak izin
        if ($request->has('error')) {
            $message = match ($request->input('error')) {
                'access_denied' => 'Login dibatalkan. Anda harus memberikan izin untuk masuk.',
                default => 'Terjadi kesalahan SSO: ' . $request->input('error_description', 'Unknown error'),
            };
            return redirect()->route('login')->withErrors(['sso' => $message]);
        }

        try {
            $ssoUser = Socialite::driver('sipetra')->user();
        } catch (\Exception $e) {
            logger()->error('SSO Login Failed: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['sso' => 'Gagal login via SSO SIPETRA.']);
        }

        $accessToken  = $ssoUser->token;
        $refreshToken = $ssoUser->refreshToken;

        // Data dari /api/user/me (sudah lengkap karena provider menggunakan endpoint ini)
        $rawData = $ssoUser->getRaw();

        // Data profil dari response
        $profile      = $rawData['profile'] ?? [];
        $organization = $rawData['organization'] ?? [];

        // === STRATEGI LINKING ===
        // Cari user lokal berdasarkan sipetra_id, lalu fallback ke email
        $localUser = User::where('sipetra_id', $ssoUser->getId())->first()
                  ?? User::where('email', $ssoUser->getEmail())->first();

        $userData = [
            'sipetra_id'            => $ssoUser->getId(),
            'name'                  => $ssoUser->getName(),
            'email'                 => $ssoUser->getEmail(),
            'sipetra_token'         => $accessToken,
            'sipetra_refresh_token' => $refreshToken,
            'avatar_url'            => $ssoUser->getAvatar(),

            // Identity (dari profile)
            'identity_type'  => $profile['identity_type'] ?? null,
            'nip'            => $profile['nip'] ?? null,
            'nip_baru'       => $profile['nip_baru'] ?? null,
            'sobat_id'       => $profile['sobat_id'] ?? null,
            'jenis_kelamin'  => $profile['jenis_kelamin'] ?? null,
            'tempat_lahir'   => $profile['tempat_lahir'] ?? null,
            'tanggal_lahir'  => $profile['tanggal_lahir'] ?? null,
            'pendidikan'     => $profile['pendidikan'] ?? null,

            // Organization
            'kd_satker'  => $organization['kd_satker'] ?? null,
            'jabatan'    => $organization['jabatan'] ?? null,
            'unit_kerja' => $organization['unit_kerja'] ?? null,
            'golongan'   => $organization['golongan'] ?? null,
        ];

        if ($localUser) {
            $localUser->update($userData);
        } else {
            $userData['password'] = null; // SSO-only user
            $localUser = User::create($userData);
        }

        Auth::login($localUser);

        return redirect()->intended(route('dashboard'));
    }
}
```

### 4.6. Daftarkan Route

Di `routes/web.php`:

```php
use App\Http\Controllers\Auth\SsoController;

Route::get('/auth/sipetra/redirect',  [SsoController::class, 'redirect'])->name('sipetra.login');
Route::get('/auth/sipetra/callback', [SsoController::class, 'callback'])->name('sipetra.callback');
```

### 4.7. Tambahkan Tombol Login SSO di Halaman Login

```html
<a href="{{ route('sipetra.login') }}"
   class="btn btn-primary">
    🔐 Login dengan SIPETRA SSO
</a>
```

---

## 5. Integrasi Non-Laravel (Generic OAuth2)

Untuk aplikasi **Next.js, Vue, PHP Native, Go, Python**, dll — ikuti protokol OAuth2 standar:

### 5.1. Redirect User ke Halaman Login SIPETRA

Saat user klik "Login SSO", redirect browser ke:

```
GET https://sipetra.test/oauth/authorize
    ?client_id=YOUR_CLIENT_ID
    &redirect_uri=https://your-app.com/auth/callback
    &response_type=code
    &scope=profile:read identity:read organization:read
    &state=RANDOM_STRING_UNTUK_CSRF_PROTECTION
```

| Parameter      | Wajib | Keterangan |
|---------------|-------|------------|
| `client_id`    | Ya    | ID OAuth Client dari admin SIPETRA |
| `redirect_uri` | Ya    | URL callback yang terdaftar |
| `response_type`| Ya    | Selalu `code` |
| `scope`        | Ya    | Scope yang diminta (lihat [Bagian 6](#6-referensi-api-endpoint-sipetra)) |
| `state`        | Sangat direkomendasikan | Random string untuk mencegah CSRF |

### 5.2. Tangkap Authorization Code

Setelah user login dan menyetujui, SIPETRA redirect kembali ke:

```
https://your-app.com/auth/callback?code=AUTH_CODE_xxx&state=xxx
```

### 5.3. Tukar Code dengan Access Token

**Server-side** (jangan di frontend!), kirim POST request:

```bash
POST https://sipetra.test/oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code
&client_id=YOUR_CLIENT_ID
&client_secret=YOUR_CLIENT_SECRET
&redirect_uri=https://your-app.com/auth/callback
&code=AUTH_CODE_xxx
```

**Response sukses:**
```json
{
    "token_type": "Bearer",
    "expires_in": 3600,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "refresh_token": "def50200abc123..."
}
```

### 5.4. Ambil Data User

Gunakan `access_token` untuk mengakses API:

```bash
GET https://sipetra.test/api/user/me
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOi...
Accept: application/json
```

---

## 6. Referensi API Endpoint SIPETRA

Semua endpoint memerlukan header `Authorization: Bearer {access_token}`.

### `GET /api/user` — Profil Dasar
**Scope**: `profile:read`

```json
{
    "id": 42,
    "name": "Ahmad Fauzi",
    "email": "ahmad.fauzi@bps.go.id",
    "avatar": "https://sipetra.test/avatars/42.jpg"
}
```

### `GET /api/user/me` — Profil Lengkap (Rekomendasi)
**Scope**: `profile:read`

```json
{
    "id": 42,
    "name": "Ahmad Fauzi",
    "email": "ahmad.fauzi@bps.go.id",
    "avatar": "https://sipetra.test/avatars/42.jpg",
    "client_role": null,
    "profile": {
        "identity_type": "pegawai",
        "nip": "199001012020011001",
        "nip_baru": "199001012020011001",
        "sobat_id": null,
        "jenis_kelamin": "LK",
        "tempat_lahir": "Jakarta",
        "tanggal_lahir": "1990-01-01",
        "pendidikan": "S1"
    },
    "organization": {
        "kd_satker": "33210",
        "unit_kerja": "BPS Kabupaten Demak",
        "jabatan": "Statistisi Ahli Pertama",
        "golongan": "III/b"
    }
}
```

### `GET /api/user/identity` — Data Identitas
**Scope**: `identity:read`

```json
{
    "identity_type": "pegawai",
    "nip": "199001012020011001",
    "nip_baru": "199001012020011001",
    "sobat_id": null,
    "jenis_kelamin": "LK",
    "tempat_lahir": "Jakarta",
    "tanggal_lahir": "1990-01-01",
    "pendidikan": "S1"
}
```

### `GET /api/user/organization` — Data Organisasi
**Scope**: `organization:read`

```json
{
    "kd_satker": "33210",
    "unit_kerja": "BPS Kabupaten Demak",
    "jabatan": "Statistisi Ahli Pertama",
    "golongan": "III/b"
}
```

### Daftar Scope Tersedia

| Scope | Deskripsi |
|-------|-----------|
| `profile:read` | Baca profil dasar (nama, email, avatar) |
| `identity:read` | Baca data identitas (NIP, SOBAT ID, tipe) |
| `organization:read` | Baca info organisasi (satker, unit kerja, jabatan) |
| `phone:read` | Baca nomor telepon |
| `email:read` | Baca alamat email |
| `user:manage` | Akses penuh manajemen user (khusus admin) |

---

## 7. Strategi Linking User Lokal

### Migrasi Database Klien

Tambahkan kolom berikut ke tabel `users` aplikasi klien Anda:

```php
Schema::table('users', function (Blueprint $table) {
    // Kolom wajib untuk SSO
    $table->unsignedBigInteger('sipetra_id')->nullable()->unique();
    $table->text('sipetra_token')->nullable();
    $table->text('sipetra_refresh_token')->nullable();

    // Kolom profil (optional, untuk sinkronisasi)
    $table->string('identity_type')->nullable();    // pegawai|mitra|admin
    $table->string('nip')->nullable();
    $table->string('nip_baru')->nullable();
    $table->string('sobat_id')->nullable();
    $table->string('kd_satker')->nullable();
    $table->string('jabatan')->nullable();
    $table->string('unit_kerja')->nullable();
    $table->string('golongan')->nullable();
    $table->string('jenis_kelamin', 2)->nullable();
    $table->string('tempat_lahir')->nullable();
    $table->date('tanggal_lahir')->nullable();
    $table->string('pendidikan')->nullable();
    $table->string('phone')->nullable();
    $table->string('avatar_url')->nullable();
});
```

### Logika Linking (Pseudocode)

```
1. Terima data user dari SSO (sipetra_id, email, name, ...)
2. Cari user lokal berdasarkan sipetra_id
3. Jika tidak ditemukan, cari berdasarkan email
4. Jika ditemukan:
   - Update data profil dari SSO (sinkronisasi)
   - Simpan sipetra_id (untuk linking berikutnya)
5. Jika tidak ditemukan:
   - Buat user baru dari data SSO
   - Set password = null (user ini hanya bisa login via SSO)
6. Login user ke session lokal
```

> 💡 **Mengapa Pendekatan Ini Aman?**
> Jika `andi@bps.go.id` sudah terdaftar di aplikasi Anda (login biasa), dan kemudian dia login via SSO SIPETRA, email matching akan menemukan akun yang sama — tidak akan membuat duplikat.

---

## 8. Penanganan Token & Refresh

### Token Lifetime
- **Access Token**: 1 jam (3600 detik)
- **Refresh Token**: 30 hari
- **Personal Access Token**: 6 bulan

### Contoh Refresh Token (Laravel)

```php
$response = Http::asForm()->post(config('services.sipetra.base_url') . '/oauth/token', [
    'grant_type'    => 'refresh_token',
    'client_id'     => config('services.sipetra.client_id'),
    'client_secret' => config('services.sipetra.client_secret'),
    'refresh_token' => $user->sipetra_refresh_token,
]);

if ($response->successful()) {
    $data = $response->json();
    $user->update([
        'sipetra_token'         => $data['access_token'],
        'sipetra_refresh_token' => $data['refresh_token'],
    ]);
}
```

### Penanganan Token Expired

Jika API mengembalikan `401 Unauthorized`:
1. Coba refresh token secara otomatis
2. Jika refresh gagal (refresh token juga expired), arahkan user untuk login ulang via SSO

---

## 9. Kebijakan Akses Klien (Access Policy)

Admin SIPETRA dapat mengatur kebijakan akses per-client:

| Policy | Perilaku |
|--------|----------|
| **Open** | Semua user SIPETRA yang aktif bisa login ke aplikasi ini |
| **Restricted** | Hanya user yang cocok dengan **Access Rules** yang bisa login |

### Jenis Access Rule

| Tipe Rule | Keterangan | Contoh |
|-----------|-----------|--------|
| `user` | User spesifik berdasarkan ID | User ID 42 |
| `sipetra_role` | Berdasarkan role Spatie di SIPETRA | `super_admin`, `operator` |
| `identity_type` | Berdasarkan tipe identitas | `pegawai`, `mitra` |

> Jika user tidak memenuhi aturan akses, SIPETRA akan menampilkan halaman **"Akses Ditolak"** (HTTP 403) dan user tidak akan di-redirect kembali ke aplikasi Anda.

---

## 10. Troubleshooting

### ❌ Error: `invalid_client`
**Penyebab**: Client ID atau Client Secret salah.
**Solusi**: Periksa kembali `SIPETRA_CLIENT_ID` dan `SIPETRA_CLIENT_SECRET` di `.env`.

### ❌ Error: `invalid_grant`
**Penyebab**: Authorization code sudah kadaluarsa atau sudah digunakan.
**Solusi**: Code hanya bisa digunakan sekali dan kadaluarsa dalam beberapa menit. Ulangi proses otorisasi.

### ❌ Error: `invalid_scope`
**Penyebab**: Scope yang diminta tidak tersedia di server.
**Solusi**: Pastikan scope yang Anda minta terdaftar (lihat [Daftar Scope](#daftar-scope-tersedia)).

### ❌ Redirect URI Mismatch (HTTP 401)
**Penyebab**: `redirect_uri` di request tidak cocok dengan yang terdaftar di server.
**Solusi**: Pastikan `SIPETRA_REDIRECT_URI` di `.env` **persis** sama dengan yang terdaftar di OAuth Client SIPETRA (termasuk protokol `https://` dan trailing path).

### ❌ HTTP 403 — Akses Ditolak
**Penyebab**: Client dikonfigurasi sebagai `Restricted` dan user Anda tidak memiliki access rule yang sesuai.
**Solusi**: Hubungi admin SIPETRA untuk menambahkan access rule yang sesuai.

### ❌ Data user dari SSO adalah Super Admin (bukan user yang login)
**Penyebab**: Bug lama yang sudah diperbaiki di versi terbaru SIPETRA.
**Solusi**: Pastikan server SIPETRA menggunakan versi terbaru dengan `TokenDisplayNameResolver`.

### ❌ Token expired terlalu cepat
**Info**: Access token default bertahan 1 jam. Implementasikan mekanisme refresh token (lihat [Bagian 8](#8-penanganan-token--refresh)).

---

## Referensi Cepat

```bash
# URL Utama SIPETRA
Authorization:  https://sipetra.test/oauth/authorize
Token Exchange: https://sipetra.test/oauth/token
User Profile:   https://sipetra.test/api/user/me
User Basic:     https://sipetra.test/api/user
User Identity:  https://sipetra.test/api/user/identity
User Org:       https://sipetra.test/api/user/organization
```

```env
# Template .env Klien
SIPETRA_CLIENT_ID="xxxx"
SIPETRA_CLIENT_SECRET="xxxx"
SIPETRA_REDIRECT_URI="https://your-app.com/auth/sipetra/callback"
SIPETRA_BASE_URL="https://sipetra.test"
```

## 11. Keamanan: Penanganan Browser Cache

Salah satu masalah umum pada aplikasi web adalah user masih bisa melihat data sensitif saat menekan tombol **Back** di browser setelah **Logout**. Ini terjadi karena browser memuat halaman dari cache lokal, bukan meminta ke server.

### Solusi: Middleware No-Cache

Anda **sangat disarankan** menerapkan middleware pada aplikasi klien Anda untuk mengirim header `Cache-Control`.

#### Langkah 1: Buat Middleware
Buat file `app/Http/Middleware/NoCacheHeaders.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
```

#### Langkah 2: Daftarkan ke Group Web
Di `bootstrap/app.php` (untuk Laravel 11+) atau `Kernel.php`. Sangat disarankan untuk memasukkannya ke group `web` agar mencakup seluruh rute aplikasi:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->appendToGroup('web', [
        \App\Http\Middleware\NoCacheHeaders::class,
    ]);
})
```

#### Langkah 3: Gunakan Null-Safe Operator di Blade
Sebagai langkah antisipasi tambahan, selalu gunakan **null-safe operator** (`?->`) saat mengakses properti user di dalam file `.blade.php`. Hal ini mencegah error "Attempt to read property on null" jika user menekan tombol Back tepat saat session berakhir:

```html
<!-- Contoh di Blade -->
<span>{{ Auth::user()?->name }}</span>
```

Dengan kombinasi dua langkah di atas, setiap kali user menekan tombol Back setelah logout, browser dipaksa meminta ulang ke server, dan middleware `auth` akan otomatis me-redirect user ke halaman login tanpa menyebabkan crash pada aplikasi.


