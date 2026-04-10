# Panduan OAuth Client & Integrasi SSO — SIPETRA BPS

## Daftar Isi
- [Status Halaman Login](#status-halaman-login)
- [Struktur UI Admin Panel](#struktur-ui-admin-panel)
- [Membuat OAuth Client via UI](#membuat-oauth-client-via-ui)
- [Membuat OAuth Client via CLI](#membuat-oauth-client-via-cli)
- [Menguji OAuth Flow (Manual)](#menguji-oauth-flow-manual)
- [Menguji OAuth Flow (Automated Test)](#menguji-oauth-flow-automated-test)
- [Panduan Integrasi ke Existing App](#panduan-integrasi-ke-existing-app)
- [Referensi Endpoint API](#referensi-endpoint-api)
- [Troubleshooting](#troubleshooting)
- [Keamanan](#keamanan)

---

## Status Halaman Login

| Halaman | Path | Komponen | Status |
|---------|------|----------|--------|
| Custom Login Publik | `/login` | `App\Livewire\Auth\Login` | ✅ Custom (NIP/Email toggle) |
| Admin Login | `/admin/login` | `Filament\Auth\Login` | ✅ Bawaan + Branding BPS |
| Consent OAuth | `/oauth/authorize` | `vendor/passport/authorize.blade.php` | ✅ Custom BPS |
| Dashboard User | `/dashboard` | `dashboard.blade.php` | ✅ Profil SSO |

### Cara Edit Admin Login (`/admin/login`)

**Cara 1: Via `AdminPanelProvider.php` (Ringan)**
```php
->brandName('SIPETRA - BPS')
->brandLogo(asset('logoBpsDemakHitam.png'))
->brandLogoHeight('2rem')
->colors(['primary' => Color::Blue])
```

**Cara 2: Override Full Custom**
1. Buat `app/Filament/Pages/Auth/Login.php` yang extends `Filament\Auth\Login`
2. Override method yang diinginkan
3. Daftarkan: `->login(\App\Filament\Pages\Auth\Login::class)`

---

## Struktur UI Admin Panel

| Menu | Sumber | Fungsi |
|------|--------|--------|
| **Dashboard** | Filament Bawaan | Halaman utama + widget |
| **Users** | `App\Filament\Resources\Users\UserResource` | Kelola user (pegawai/mitra/admin) |
| **OAuth Clients** | `App\Filament\Resources\OAuthClients\OAuthClientResource` | ✅ **Custom** — Buat & kelola OAuth Clients |
| **Roles** | `FilamentShield` | Kelola roles & permissions |

> **Catatan:** Plugin `n3xt0r/filament-passport-ui` sudah dinonaktifkan dan digantikan oleh resource custom karena plugin tersebut memiliki keterbatasan — tidak ada field redirect URI, scopes kosong, dan secret tidak ditampilkan setelah create.

---

## Membuat OAuth Client via UI

### Step 1: Login ke Admin Panel
- URL: `http://sipetra.test/admin/login`
- Email: `admin@gmail.com` / Password: `admin`

### Step 2: Navigasi ke OAuth Clients
- Sidebar kiri → grup **"OAuth Management"** → klik **"OAuth Clients"**

### Step 3: Klik "Buat Client Baru"

Form yang tampil memiliki 4 section:

#### Section 1: Client Details

| Field | Isian | Keterangan |
|-------|-------|------------|
| **Nama Client** | `Aplikasi Survei BPS` | Nama yang tampil di halaman consent SSO |
| **Grant Type** | `Authorization Code (SSO)` | Untuk SSO standar. Pilihan lain: Client Credentials (M2M), Personal Access Token |
| **Owner** | Pilih user (searchable) | User pemilik client. Hanya muncul untuk grant type Authorization Code & Personal Access |

#### Section 2: Redirect URIs

| Field | Isian | Keterangan |
|-------|-------|------------|
| **Redirect URIs** | `http://localhost:8001/auth/callback` | Ketik URL lalu **tekan Enter**. Bisa lebih dari satu. **Wajib** untuk Authorization Code |

> [!IMPORTANT]
> Redirect URI **harus sama persis** dengan yang digunakan oleh aplikasi client (termasuk http/https, trailing slash, port).

#### Section 3: Scopes

| Scope | Data yang Didapat |
|-------|-------------------|
| `profile:read` | Nama, email, avatar *(default)* |
| `identity:read` | NIP, NIP Baru, SOBAT ID, tipe identitas, jenis kelamin, TTL, pendidikan |
| `organization:read` | Kode satker, unit kerja, jabatan, golongan |
| `phone:read` | Nomor telepon |
| `email:read` | Alamat email |
| `user:manage` | Akses penuh manajemen user *(admin only)* |

#### Section 4: Pengaturan (Collapsed)

| Field | Default | Keterangan |
|-------|---------|------------|
| **Confidential Client** | ✅ ON | Client dengan secret. Nonaktifkan untuk SPA/mobile (public client) |
| **Revoked** | ❌ OFF | Aktifkan untuk menonaktifkan client |

### Step 4: Klik Create

Setelah create, Anda langsung diarahkan ke halaman View yang menampilkan **Client ID** dan **Client Secret** dengan tombol Copy.

> [!CAUTION]
> **SALIN Client Secret SEGERA!** Secret hanya ditampilkan **sekali** di halaman View setelah create. Jika Anda meninggalkan halaman atau refresh, secret tidak bisa dilihat lagi. Jika hilang, **hapus client dan buat ulang**.

### Step 5: Edit Client (Opsional)

Klik tombol **Edit** di halaman View untuk mengubah nama, grant type, redirect URIs, owner, scopes, atau revoke.

---

## Membuat OAuth Client via CLI

### A. Authorization Code (SSO)
```powershell
php artisan passport:client --no-interaction --name="Aplikasi Survei BPS" --redirect_uri=http://localhost:8001/auth/callback
```

### B. Public Client (SPA/Mobile)
```powershell
php artisan passport:client --no-interaction --public --name="SPA Dashboard BPS" --redirect_uri=http://localhost:3000/callback
```

### C. Client Credentials (M2M)
```powershell
php artisan passport:client --no-interaction --client --name="Microservice Sync"
```

### D. Lihat / Hapus Client
```powershell
# Lihat semua
php artisan tinker --execute 'use Laravel\Passport\Client; echo Client::all(["id","name","redirect_uris","grant_types","revoked"])->toJson(JSON_PRETTY_PRINT);'

# Revoke
php artisan tinker --execute 'use Laravel\Passport\Client; Client::find("CLIENT_ID")->update(["revoked" => true]);'

# Hapus
php artisan tinker --execute 'use Laravel\Passport\Client; Client::find("CLIENT_ID")->delete();'
```

---

## Menguji OAuth Flow (Manual)

### Prasyarat
- ✅ OAuth Client dengan Client ID, Secret, dan Redirect URI terisi
- ✅ Server SIPETRA berjalan (`php artisan serve` atau Herd)

### Tahap 1: Authorization Request (Browser)

Buka di browser (ganti `CLIENT_ID` dan `REDIRECT_URI`):

```
http://sipetra.test/oauth/authorize?client_id=CLIENT_ID&redirect_uri=REDIRECT_URI&response_type=code&scope=profile:read&state=random-test-string
```

**Yang terjadi:**
1. Belum login → redirect ke `/login`
2. Login dengan NIP/email
3. Tampil halaman consent → klik **"Izinkan Akses"**
4. Redirect ke `REDIRECT_URI?code=AUTHORIZATION_CODE&state=random-test-string`
5. Browser error (redirect URI belum ada) — **normal!**
6. **Salin nilai `code=...` dari URL bar**

> [!IMPORTANT]
> Authorization code hanya berlaku **~30 detik**. Segera lanjut ke Tahap 2!

### Tahap 2: Tukar Code → Access Token

**PowerShell:**
```powershell
$body = @{
    grant_type    = "authorization_code"
    client_id     = "CLIENT_ID"
    client_secret = "CLIENT_SECRET"
    redirect_uri  = "REDIRECT_URI"
    code          = "AUTHORIZATION_CODE"
}
Invoke-RestMethod -Method Post -Uri "http://sipetra.test/oauth/token" -Body $body
```

**curl:**
```bash
curl -X POST http://sipetra.test/oauth/token \
  -d "grant_type=authorization_code" \
  -d "client_id=CLIENT_ID" \
  -d "client_secret=CLIENT_SECRET" \
  -d "redirect_uri=REDIRECT_URI" \
  -d "code=AUTHORIZATION_CODE"
```

**Response sukses:**
```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1Q...",
  "refresh_token": "def50200..."
}
```

### Tahap 3: Akses API dengan Token

```powershell
$headers = @{ Authorization = "Bearer ACCESS_TOKEN" }
Invoke-RestMethod -Uri "http://sipetra.test/api/user" -Headers $headers
```

---

## Menguji OAuth Flow (Automated Test)

Seluruh flow SSO sudah dicakup oleh automated test di `tests/Feature/OAuthFlowTest.php`.

```powershell
php artisan test --filter=OAuthFlowTest
```

| Test | Yang Diuji |
|------|------------|
| Consent page tampil | `GET /oauth/authorize` → 200, nama client muncul |
| Approve → dapat auth code | POST approve → redirect dengan `code=` dan `state=` |
| Full E2E flow | Authorize → token → API → refresh |
| API `/api/user` | `profile:read` scope → data user |
| API `/api/user/identity` | `identity:read` scope → data identitas |
| API `/api/user/organization` | `organization:read` scope → data organisasi |
| Tanpa token → 401 | Unauthorized |
| Redirect URI salah → ditolak | Reject URI yang tidak terdaftar |
| Client secret salah → ditolak | Token exchange gagal |

---

## Panduan Integrasi ke Existing App

Berikut garis besar langkah integrasi SSO SIPETRA ke aplikasi yang sudah ada, terlepas dari stack teknologinya.

### Alur Universal (Semua Stack)

```
┌─────────────┐    1. Redirect    ┌──────────────────┐
│  App Client  │ ──────────────→ │  SIPETRA SSO      │
│  (JS/PHP/Py) │                  │  /oauth/authorize │
└─────────────┘                  └──────────────────┘
       ↑                                  │
       │ 4. API call                      │ 2. User login + consent
       │    /api/user                     │
       │                                  ↓
┌─────────────┐    3. POST       ┌──────────────────┐
│  App Client  │ ←────────────── │  Redirect URI     │
│  Backend     │   code → token  │  ?code=xxx        │
└─────────────┘                  └──────────────────┘
```

### Langkah-langkah

#### 1. Daftarkan App sebagai OAuth Client
- Login admin → buat client di menu OAuth Clients
- Catat **Client ID**, **Client Secret**, dan **Redirect URI**
- Simpan Client ID & Secret di `.env` app client

#### 2. Redirect User ke SIPETRA
Saat user klik "Login dengan SIPETRA", redirect ke:
```
http://sipetra.test/oauth/authorize?
  client_id=CLIENT_ID
  &redirect_uri=http://app-anda.test/auth/callback
  &response_type=code
  &scope=profile:read identity:read
  &state=RANDOM_STRING
```

#### 3. Tangkap Callback & Tukar Code
Setelah user approve, SIPETRA redirect ke `redirect_uri` Anda dengan `?code=xxx&state=yyy`. Backend Anda harus:
1. Verifikasi `state` cocok
2. POST ke `http://sipetra.test/oauth/token` dengan `code` tersebut
3. Simpan `access_token` dan `refresh_token`

#### 4. Ambil Data User
Gunakan `access_token` untuk call API:
```
GET http://sipetra.test/api/user
Authorization: Bearer ACCESS_TOKEN
```

#### 5. Cocokkan & Sinkronisasi User Lokal

Ini bagian krusial — bagaimana mencocokkan user dari SIPETRA dengan database lokal yang sudah ada.

---

### Strategi Pencocokan User (Existing Database)

Skenario umum: app Anda sudah punya tabel `users` dengan data yang mungkin overlap dengan SIPETRA. Berikut strategi yang direkomendasikan:

```
┌──────────────────────────────────────────────────────────────────┐
│                  USER LOGIN VIA SIPETRA SSO                      │
│                                                                  │
│  1. Dapat access_token dari SIPETRA                             │
│  2. GET /api/user → { id, name, email, nip, ... }               │
│                                                                  │
│  3. Cari di DB lokal:                                           │
│     ├─ Punya kolom sipetra_id? → Cari by sipetra_id             │
│     ├─ Cocok? → UPDATE profil, LOGIN ✅                          │
│     │                                                            │
│     ├─ Tidak cocok sipetra_id? → Cari by EMAIL                  │
│     ├─ Cocok email? → LINK (isi sipetra_id), UPDATE, LOGIN ✅    │
│     │                                                            │
│     └─ Tidak ada sama sekali? → CREATE user baru, LOGIN ✅       │
└──────────────────────────────────────────────────────────────────┘
```

#### Prioritas Pencocokan

| Urutan | Cocokkan by | Aksi | Keterangan |
|--------|-------------|------|------------|
| 1 | `sipetra_id` | Update profil + login | Link permanen, paling aman |
| 2 | `email` | Link sipetra_id + update + login | Untuk user existing yang belum pernah SSO |
| 3 | `nip` (opsional) | Link sipetra_id + update + login | Jika app punya data NIP |
| 4 | Tidak ditemukan | Create user baru + login | User baru dari SIPETRA |

#### Persiapan Database (Migration)

Tambahkan kolom `sipetra_id` ke tabel users di app client:

```sql
-- Migration di app client
ALTER TABLE users ADD COLUMN sipetra_id VARCHAR(255) NULL UNIQUE;
ALTER TABLE users ADD COLUMN sipetra_token TEXT NULL;
ALTER TABLE users ADD COLUMN sipetra_refresh_token TEXT NULL;
```

> [!IMPORTANT]
> Kolom `sipetra_id` menyimpan ID user dari server SIPETRA. Ini menjadi **link permanen** antara user lokal dan SIPETRA. Setelah di-link pertama kali (via email matching), login berikutnya langsung cocok via `sipetra_id`.

#### Logika Pencocokan (Pseudocode)

```
function handleSipetraCallback(sipetra_user):
    // Prioritas 1: Cari by sipetra_id (user yang sudah pernah SSO)
    local_user = db.users.find_by(sipetra_id: sipetra_user.id)

    if NOT local_user:
        // Prioritas 2: Cari by email (user existing yang belum pernah SSO)
        local_user = db.users.find_by(email: sipetra_user.email)

    if NOT local_user:
        // Prioritas 3 (opsional): Cari by NIP jika app punya kolom NIP
        if sipetra_user.nip:
            local_user = db.users.find_by(nip: sipetra_user.nip)

    if local_user:
        // User ditemukan → link & update profil dari SIPETRA
        local_user.update(
            sipetra_id: sipetra_user.id,
            name: sipetra_user.name,
            email: sipetra_user.email,    // sync email terbaru
            // ... field lain sesuai kebutuhan
        )
    else:
        // User baru → buat dari data SIPETRA
        local_user = db.users.create(
            sipetra_id: sipetra_user.id,
            name: sipetra_user.name,
            email: sipetra_user.email,
            // password tidak perlu karena login via SSO
        )

    login(local_user)
```

> [!TIP]
> **Mana field yang di-sync?** Terserah Anda. Bisa sync semua (nama, email) dari SIPETRA setiap login, atau hanya sync saat pertama kali. Rekomendasi: **selalu sync nama dan email** agar data tetap konsisten dengan data kepegawaian di BPS.

> [!WARNING]
> **Jangan sync password!** User SSO tidak perlu password di app client. Jika app Anda juga support login non-SSO (email+password), biarkan kolom password tetap untuk fallback login.

---

### Contoh per Stack

#### JavaScript / Node.js (Express)

```env
# .env
SIPETRA_CLIENT_ID=your-client-id
SIPETRA_CLIENT_SECRET=your-client-secret
SIPETRA_REDIRECT_URI=http://localhost:3000/auth/callback
SIPETRA_BASE_URL=http://sipetra.test
```

```javascript
// routes/auth.js
const axios = require('axios');

// Step 1: Redirect ke SIPETRA
app.get('/auth/login', (req, res) => {
  const state = crypto.randomUUID();
  req.session.oauthState = state;

  const params = new URLSearchParams({
    client_id: process.env.SIPETRA_CLIENT_ID,
    redirect_uri: process.env.SIPETRA_REDIRECT_URI,
    response_type: 'code',
    scope: 'profile:read identity:read',
    state: state,
  });

  res.redirect(`${process.env.SIPETRA_BASE_URL}/oauth/authorize?${params}`);
});

// Step 2: Callback — tukar code → token → cocokkan user
app.get('/auth/callback', async (req, res) => {
  if (req.query.state !== req.session.oauthState) {
    return res.status(403).send('State mismatch');
  }

  // Tukar code → token
  const tokenRes = await axios.post(`${process.env.SIPETRA_BASE_URL}/oauth/token`, {
    grant_type: 'authorization_code',
    client_id: process.env.SIPETRA_CLIENT_ID,
    client_secret: process.env.SIPETRA_CLIENT_SECRET,
    redirect_uri: process.env.SIPETRA_REDIRECT_URI,
    code: req.query.code,
  });

  const { access_token, refresh_token } = tokenRes.data;

  // Ambil data user dari SIPETRA
  const sipetraUser = (await axios.get(`${process.env.SIPETRA_BASE_URL}/api/user`, {
    headers: { Authorization: `Bearer ${access_token}` },
  })).data;

  // Cocokkan dengan database lokal
  let localUser = await db('users').where('sipetra_id', sipetraUser.id).first();

  if (!localUser) {
    // Cari by email (user existing yang belum pernah SSO)
    localUser = await db('users').where('email', sipetraUser.email).first();
  }

  if (localUser) {
    // User ditemukan → link & update
    await db('users').where('id', localUser.id).update({
      sipetra_id: sipetraUser.id,
      name: sipetraUser.name,
      email: sipetraUser.email,
      sipetra_token: access_token,
      sipetra_refresh_token: refresh_token,
    });
  } else {
    // User baru → create dari data SIPETRA
    const [id] = await db('users').insert({
      sipetra_id: sipetraUser.id,
      name: sipetraUser.name,
      email: sipetraUser.email,
      sipetra_token: access_token,
      sipetra_refresh_token: refresh_token,
    });
    localUser = { id };
  }

  req.session.userId = localUser.id;
  res.redirect('/dashboard');
});
```

#### PHP (Laravel — sebagai Client App)

```env
# .env di app client
SIPETRA_CLIENT_ID=your-client-id
SIPETRA_CLIENT_SECRET=your-client-secret
SIPETRA_REDIRECT_URI=http://localhost:8001/auth/callback
SIPETRA_BASE_URL=http://sipetra.test
```

```php
// routes/web.php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

Route::get('/auth/login', function () {
    $state = Str::random(40);
    session(['oauth_state' => $state]);

    return redirect(config('services.sipetra.base_url') . '/oauth/authorize?' . http_build_query([
        'client_id' => config('services.sipetra.client_id'),
        'redirect_uri' => config('services.sipetra.redirect_uri'),
        'response_type' => 'code',
        'scope' => 'profile:read identity:read',
        'state' => $state,
    ]));
});

Route::get('/auth/callback', function () {
    abort_unless(request('state') === session('oauth_state'), 403, 'State mismatch');

    $baseUrl = config('services.sipetra.base_url');

    // Tukar code → token
    $tokenResponse = Http::asForm()->post("$baseUrl/oauth/token", [
        'grant_type' => 'authorization_code',
        'client_id' => config('services.sipetra.client_id'),
        'client_secret' => config('services.sipetra.client_secret'),
        'redirect_uri' => config('services.sipetra.redirect_uri'),
        'code' => request('code'),
    ]);

    $accessToken = $tokenResponse->json('access_token');
    $refreshToken = $tokenResponse->json('refresh_token');

    // Ambil data user dari SIPETRA
    $sipetraUser = Http::withToken($accessToken)->get("$baseUrl/api/user")->json();

    // Cocokkan: cari by sipetra_id dulu, lalu by email
    $localUser = User::where('sipetra_id', $sipetraUser['id'])->first()
              ?? User::where('email', $sipetraUser['email'])->first();

    if ($localUser) {
        // User ditemukan → link sipetra_id & sync profil
        $localUser->update([
            'sipetra_id' => $sipetraUser['id'],
            'name' => $sipetraUser['name'],
            'email' => $sipetraUser['email'],
            'sipetra_token' => $accessToken,
            'sipetra_refresh_token' => $refreshToken,
        ]);
    } else {
        // User baru → buat dari data SIPETRA
        $localUser = User::create([
            'sipetra_id' => $sipetraUser['id'],
            'name' => $sipetraUser['name'],
            'email' => $sipetraUser['email'],
            'sipetra_token' => $accessToken,
            'sipetra_refresh_token' => $refreshToken,
            // password tidak perlu untuk SSO-only user
        ]);
    }

    Auth::login($localUser);
    return redirect('/dashboard');
});
```

#### Python (Flask)

```python
# .env
SIPETRA_CLIENT_ID=your-client-id
SIPETRA_CLIENT_SECRET=your-client-secret
SIPETRA_REDIRECT_URI=http://localhost:5000/auth/callback
SIPETRA_BASE_URL=http://sipetra.test
```

```python
# app.py
import os, uuid, requests
from flask import Flask, redirect, request, session, url_for

app = Flask(__name__)

BASE_URL = os.getenv('SIPETRA_BASE_URL')
CLIENT_ID = os.getenv('SIPETRA_CLIENT_ID')
CLIENT_SECRET = os.getenv('SIPETRA_CLIENT_SECRET')
REDIRECT_URI = os.getenv('SIPETRA_REDIRECT_URI')

@app.route('/auth/login')
def login():
    state = str(uuid.uuid4())
    session['oauth_state'] = state

    params = {
        'client_id': CLIENT_ID,
        'redirect_uri': REDIRECT_URI,
        'response_type': 'code',
        'scope': 'profile:read identity:read',
        'state': state,
    }
    from urllib.parse import urlencode
    return redirect(f"{BASE_URL}/oauth/authorize?{urlencode(params)}")

@app.route('/auth/callback')
def callback():
    assert request.args.get('state') == session.pop('oauth_state', None), 'State mismatch'

    # Tukar code → token
    token_res = requests.post(f"{BASE_URL}/oauth/token", data={
        'grant_type': 'authorization_code',
        'client_id': CLIENT_ID,
        'client_secret': CLIENT_SECRET,
        'redirect_uri': REDIRECT_URI,
        'code': request.args['code'],
    })
    token_data = token_res.json()
    access_token = token_data['access_token']
    refresh_token = token_data.get('refresh_token')

    # Ambil data user dari SIPETRA
    sipetra_user = requests.get(f"{BASE_URL}/api/user", headers={
        'Authorization': f'Bearer {access_token}',
    }).json()

    # Cocokkan dengan database lokal
    local_user = User.query.filter_by(sipetra_id=sipetra_user['id']).first()

    if not local_user:
        # Cari by email (user existing yang belum pernah SSO)
        local_user = User.query.filter_by(email=sipetra_user['email']).first()

    if local_user:
        # User ditemukan → link & update
        local_user.sipetra_id = sipetra_user['id']
        local_user.name = sipetra_user['name']
        local_user.email = sipetra_user['email']
        local_user.sipetra_token = access_token
        local_user.sipetra_refresh_token = refresh_token
        db.session.commit()
    else:
        # User baru → create dari data SIPETRA
        local_user = User(
            sipetra_id=sipetra_user['id'],
            name=sipetra_user['name'],
            email=sipetra_user['email'],
            sipetra_token=access_token,
            sipetra_refresh_token=refresh_token,
        )
        db.session.add(local_user)
        db.session.commit()

    login_user(local_user)
    return redirect('/dashboard')
```

### Catatan Integrasi

> [!TIP]
> - **State parameter** wajib digunakan untuk mencegah CSRF attack
> - **Refresh token** — simpan di database, gunakan untuk minta access token baru tanpa login ulang
> - **Scope** — minta hanya scope yang dibutuhkan, jangan semua
> - **Token expiry** — access token expired dalam 1 jam, refresh token 30 hari

> [!WARNING]
> - Client Secret **JANGAN** di-hardcode atau masuk ke git. Selalu simpan di `.env`
> - Di production, **WAJIB** gunakan HTTPS untuk callback URL
> - Validasi `state` parameter **WAJIB** — jangan skip

---

## Referensi Endpoint API

| Endpoint | Method | Fungsi | Scope |
|----------|--------|--------|-------|
| `/oauth/authorize` | GET | Memulai flow login SSO | — |
| `/oauth/token` | POST | Tukar auth code → access token | — |
| `/oauth/token` | POST | Refresh token (`grant_type=refresh_token`) | — |
| `/api/user` | GET | Data profil (nama, email) | `profile:read` |
| `/api/user/identity` | GET | Data identitas (NIP, SOBAT ID, dll) | `identity:read` |
| `/api/user/organization` | GET | Data organisasi (satker, jabatan, dll) | `organization:read` |

---

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---------|----------|-------|
| `invalid_client` | Client ID/Secret salah | Cek ulang kredensial |
| `invalid_redirect` | Redirect URI tidak cocok | Harus **PERSIS** sama (http/https, trailing slash, port) |
| `invalid_grant` | Auth code expired | Code hanya valid ~30 detik, segera tukar |
| Consent tidak muncul | User belum login | Login dulu di `/login` |
| Secret hilang | Sudah meninggalkan halaman View | Hapus client, buat ulang |
| Error saat create | Field wajib belum diisi | Pastikan nama, grant type, dan redirect URI terisi |
| `State mismatch` | State tidak cocok | Pastikan state disimpan di session sebelum redirect |
| `Token expired` | Access token kadaluarsa (1 jam) | Gunakan refresh token untuk minta token baru |

---

## Keamanan

1. **HTTPS wajib** di production untuk semua URL (SIPETRA + app client)
2. **Client Secret** — simpan di `.env` app client, JANGAN hardcode atau commit ke git
3. **OAuth Private Key** (`storage/oauth-private.key`) — JANGAN share, server SIPETRA only
4. **Token expiration** — Access: 1 jam, Refresh: 30 hari, Personal: 6 bulan
5. **State parameter** — WAJIB digunakan dan divalidasi untuk mencegah CSRF

---

## Struktur File Resource Custom (Developer)

```
app/Filament/Resources/OAuthClients/
├── OAuthClientResource.php          # Resource utama (model, navigasi, pages)
├── Pages/
│   ├── ListOAuthClients.php         # Halaman list + tombol "Buat Client Baru"
│   ├── CreateOAuthClient.php        # Halaman create + generate secret
│   ├── ViewOAuthClient.php          # Halaman view + tampilkan secret (sekali)
│   └── EditOAuthClient.php          # Halaman edit client
├── Schemas/
│   └── OAuthClientForm.php          # Form fields (name, grant type, redirect URIs, scopes)
└── Tables/
    └── OAuthClientsTable.php        # Kolom tabel (name, owner, grant types, redirect URIs)
```
