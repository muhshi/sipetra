# SSO Sipetra — Fase 2: Rule-Based Access Control & User Attributes
id, secret, link callback, managemen akses, user ABC bisa aplikasi apa saja, pegawai aplikasi ABC, mitra aplikasi A
> **Status:** 📋 Planning  
> **Tanggal:** 2026-04-13  
> **Dibuat oleh:** Admin Sipetra

---

## 1. Latar Belakang

OAuth2 SSO sudah berjalan (Passport + Filament). Sekarang perlu dua fitur lanjutan:

1. **Access Control** — Tidak semua user boleh login ke semua aplikasi client. Admin Sipetra harus bisa mengatur siapa yang boleh masuk ke masing-masing aplikasi, dengan cara yang fleksibel.
2. **User Attributes** — Aplikasi client perlu bisa mengambil atribut lengkap user (NIP, jabatan, unit kerja, dll.) dari Sipetra melalui API.

---

## 2. Keputusan Desain

### 2.1 Behavior "Tanpa Rule"

Dikontrol oleh kolom `access_policy` pada saat client OAuth dibuat:

| Nilai | Artinya |
|-------|---------|
| `restricted` | Wajib ada aturan yang cocok. Jika tidak ada → **ditolak** *(default, paling aman)* |
| `open` | Semua user aktif Sipetra boleh login tanpa perlu terdaftar |

### 2.2 Tipe Aturan Akses (Rule Types)

Admin bisa menambahkan kombinasi aturan untuk tiap aplikasi:

| Tipe | Nilai | Contoh Penggunaan |
|------|-------|-------------------|
| `user` | `user_id` | Hanya Fulan (by nama/NIP) yang boleh masuk |
| `sipetra_role` | nama role Spatie | Semua user dengan role `pegawai_bps` di Sipetra |
| `identity_type` | `pegawai` / `mitra` / `admin` | Semua Mitra Statistik boleh masuk |

**Evaluasi:** Jika user cocok dengan **salah satu** aturan → diizinkan.

### 2.3 Contoh Konfigurasi

```
Aplikasi: AL FATH
access_policy: restricted
Aturan:
  ├── identity_type = pegawai   → semua pegawai dapat role "staff"
  ├── sipetra_role = admin      → semua admin Sipetra dapat role "superadmin"
  └── user = [ID Fulan]         → Fulan (mitra khusus) juga diizinkan masuk
```

---

## 3. Skema Database

### Tabel Baru: `client_access_rules`

```sql
CREATE TABLE client_access_rules (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id        CHAR(36) NOT NULL,
    rule_type        VARCHAR(50) NOT NULL,   -- 'user' | 'sipetra_role' | 'identity_type'
    rule_value       VARCHAR(255) NOT NULL,  -- value dinamis sesuai rule_type
    client_role_id   BIGINT UNSIGNED NULL,   -- role di app client (nullable)
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,

    FOREIGN KEY (client_id) REFERENCES oauth_clients(id) ON DELETE CASCADE,
    FOREIGN KEY (client_role_id) REFERENCES client_roles(id) ON DELETE SET NULL,
    INDEX (client_id, rule_type)
);
```

### Kolom Baru di `oauth_clients`

```sql
ALTER TABLE oauth_clients
ADD COLUMN access_policy VARCHAR(20) NOT NULL DEFAULT 'restricted';
-- nilai: 'open' | 'restricted'
```

### Migrasi Data Lama

Data `client_user_accesses` (whitelist lama) akan dipindahkan ke `client_access_rules` sebagai `rule_type = 'user'`, kemudian tabel lama di-drop.

---

## 4. Alur Evaluasi Akses (Runtime)

```
User mencoba OAuth login ke Client X
            ↓
  Apakah user is_active?
    └── TIDAK → TOLAK (selalu)

            ↓
  Apakah ada access_rules untuk Client X?
    ├── TIDAK ADA RULE
    │     ├── access_policy = 'open'       → ✅ IZINKAN
    │     └── access_policy = 'restricted' → ❌ TOLAK
    │
    └── ADA RULE → evaluasi satu per satu:
          rule_type = 'user'
              cocok jika: user.id == rule_value
          rule_type = 'sipetra_role'
              cocok jika: user->hasRole(rule_value)
          rule_type = 'identity_type'
              cocok jika: user.identity_type == rule_value

          Salah satu cocok → ✅ IZINKAN
          Tidak ada yang cocok → ❌ TOLAK
```

---

## 5. Komponen yang Dibangun

### 5.1 Database Layer

- [ ] **Migration** `add_access_policy_to_oauth_clients_table`
- [ ] **Migration** `create_client_access_rules_table`

### 5.2 Enums & Models

- [ ] **Enum** `app/Enums/AccessRuleType.php` — `User | SipetraRole | IdentityType`
- [ ] **Enum** `app/Enums/ClientAccessPolicy.php` — `Open | Restricted`
- [ ] **Model** `app/Models/ClientAccessRule.php`
- [ ] **Modify** `app/Models/PassportClient.php` — tambah relasi `accessRules()`, cast `access_policy`
- [ ] **Modify** `app/Models/User.php` — update `clientRoleFor()` pakai resolver

### 5.3 Business Logic

- [ ] **Service** `app/Services/AccessRuleResolver.php`
  - `isAllowed(User, PassportClient): bool`
  - `resolveClientRole(User, PassportClient): ?string`

### 5.4 OAuth Hard Block

- [ ] **Controller** `app/Http/Controllers/Auth/OAuthAuthorizationController.php`
  - Override `authorize()` milik Passport
  - Inject `AccessRuleResolver` sebelum flow berjalan
- [ ] **View** `resources/views/auth/oauth-denied.blade.php`
  - Tampilkan nama app yang ditolak
  - Pesan informatif + tombol Kembali / Logout
- [ ] **Modify** `routes/web.php` — override route `/oauth/authorize`

### 5.5 API User Attributes

- [ ] **Resource** `app/Http/Resources/UserProfileResource.php`
- [ ] **Modify** `app/Http/Controllers/Api/UserApiController.php` — tambah method `me()`
- [ ] **Modify** `routes/api.php` — tambah `GET /api/user/me`

### 5.6 Filament UI

- [ ] **Modify** `CreateClient.php` — tambah field `access_policy`
- [ ] **New** `AccessRulesRelationManager.php` — gantikan `ClientUserAccessesRelationManager`
  - Form dinamis berdasarkan `rule_type` (pakai `->live()`)
  - Tabel yang jelas menampilkan tipe + nilai rule
- [ ] Daftarkan relation manager baru di ClientResource

### 5.7 Migrasi Data

- [ ] **Seeder** `MigrateClientUserAccessesToRulesSeeder.php`
  - Pindahkan `client_user_accesses` → `client_access_rules` sebagai `rule_type=user`

---

## 6. Response Format API `/api/user/me`

### Endpoint
```
GET /api/user/me
Authorization: Bearer {token}
Scope yang dibutuhkan: profile:read
```

### Response
```json
{
  "id": 1,
  "name": "Fulanah binti Fulan",
  "email": "fulanah@bps.go.id",
  "avatar": null,
  "client_role": "operator",
  "profile": {
    "identity_type": "pegawai",
    "nip": "19900115200212200X",
    "nip_baru": "199001152002122001",
    "sobat_id": null,
    "jenis_kelamin": "P",
    "tempat_lahir": "Demak",
    "tanggal_lahir": "1990-01-15",
    "pendidikan": "S1"
  },
  "organization": {
    "kd_satker": "3321",
    "unit_kerja": "Neraca Wilayah dan Analisis Statistik",
    "jabatan": "Statistisi Pertama",
    "golongan": "IIIb"
  }
}
```

> Endpoint per-bagian (`/api/user/identity`, `/api/user/organization`) tetap ada dan dapat digunakan dengan scope yang lebih spesifik.

---

## 7. Urutan Implementasi

```
Step 1   Migration: add_access_policy_to_oauth_clients_table
Step 2   Migration: create_client_access_rules_table
Step 3   Enum: AccessRuleType + ClientAccessPolicy
Step 4   Model: ClientAccessRule
Step 5   Model: Update PassportClient + User
Step 6   Service: AccessRuleResolver
Step 7   Controller: OAuthAuthorizationController
Step 8   View: oauth-denied.blade.php
Step 9   Route: Override /oauth/authorize di web.php
Step 10  API: UserProfileResource
Step 11  API: Update UserApiController + routes/api.php
Step 12  UI: AccessRulesRelationManager
Step 13  UI: Tambah field access_policy di CreateClient
Step 14  Data: Jalankan MigrateClientUserAccessesToRulesSeeder
Step 15  Test: Jalankan semua test
```

---

## 8. Skenario Pengujian

| # | Setup | Aksi | Expected |
|---|-------|------|----------|
| 1 | `restricted`, tanpa rule | User manapun login | ❌ Halaman Ditolak |
| 2 | `open`, tanpa rule | User aktif login | ✅ Langsung masuk |
| 3 | Rule `identity_type=pegawai` | User Pegawai login | ✅ Masuk |
| 4 | Rule `identity_type=pegawai` | User Mitra login | ❌ Ditolak |
| 5 | Rule `sipetra_role=admin` | User ber-role admin login | ✅ Masuk |
| 6 | Rule `user=Fulan` + rule `identity_type=mitra` | Fulan (non-mitra) login | ✅ Masuk (match rule user) |
| 7 | User tidak aktif | Login ke app apapun | ❌ Selalu ditolak |
| 8 | `GET /api/user/me` token valid | Client request | ✅ Full JSON profile |
| 9 | `GET /api/user/me` scope salah | Client request | ❌ 403 Unauthorized |

---

## 9. File yang Akan Berubah / Dibuat

### Dibuat Baru
| File | Keterangan |
|------|-----------|
| `app/Enums/AccessRuleType.php` | Enum tipe rule |
| `app/Enums/ClientAccessPolicy.php` | Enum kebijakan akses default |
| `app/Models/ClientAccessRule.php` | Model rule akses |
| `app/Services/AccessRuleResolver.php` | Engine evaluasi akses |
| `app/Http/Controllers/Auth/OAuthAuthorizationController.php` | Override OAuth flow |
| `app/Http/Resources/UserProfileResource.php` | API Resource lengkap |
| `resources/views/auth/oauth-denied.blade.php` | Halaman penolakan |
| `database/migrations/..._add_access_policy_to_oauth_clients.php` | Migration |
| `database/migrations/..._create_client_access_rules_table.php` | Migration |
| `database/seeders/MigrateClientUserAccessesToRulesSeeder.php` | Seeder migrasi data |
| `app/Filament/.../AccessRulesRelationManager.php` | UI manajemen rule |

### Dimodifikasi
| File | Perubahan |
|------|-----------|
| `app/Models/PassportClient.php` | Relasi `accessRules()`, cast `access_policy` |
| `app/Models/User.php` | Update `clientRoleFor()` |
| `app/Http/Controllers/Api/UserApiController.php` | Tambah method `me()` |
| `routes/api.php` | Route `GET /api/user/me` |
| `routes/web.php` | Override `GET /oauth/authorize` |
| `app/Filament/Resources/ClientResource/Pages/CreateClient.php` | Field `access_policy` |

### Dihapus (Setelah Migrasi Data)
| File | Keterangan |
|------|-----------|
| `app/Filament/.../ClientUserAccessesRelationManager.php` | Digantikan AccessRulesRelationManager |
| Tabel `client_user_accesses` | Data dipindah ke `client_access_rules` |
