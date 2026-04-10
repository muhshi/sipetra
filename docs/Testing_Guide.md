# Panduan Testing Alur SSO Secara Manual

Selain file test PEST yang ada di dalam `tests/Feature`, Anda patut memastikan integrasi Passport OAuth2 bekerja layaknya di *production* melalui pengujian simulasi *browser*. 

Berikut adalah panduan membuat **Client Dummy** di sistem SIPETRA Anda dan mensimulasikan login SSO:

## Langkah 1: Buat Dummy Client di SIPETRA

Anda bisa membuat Client ID untuk aplikasi pengetes dari Command Line Artisan atau Panel Admin SIPETRA.

**Via Artisan:**
1. Jalankan perintah pembuatan client:
   ```bash
   php artisan passport:client
   ```
2. Anda akan ditanya beberapa hal:
   - *Which user ID should the client be assigned to?:* Kosongkan saja (tekan Enter).
   - *What should we name the client?:* Ketik **"Test SSO Client"**.
   - *Where should we redirect the requests after authorization?:* Ketikkan URL lokal yang memantau callback, misalnya: **`http://localhost:8001/callback`** (meskipun aplikasi ini belum ada, tidak masalah, kita hanya ingin melihat callback URL yang membawa `code`).
3. Artisan akan memunculkan `Client ID` dan `Client Secret`. **Catat baik-baik kedua string ini**.

---

## Langkah 2: Lakukan Request Autoritas (Authorization)

1. Buka Browser (Google Chrome/Edge).
2. Akses URL berikut ini dengan mengganti teks di dalam tanda `[]` dengan data Client yang barusan didapat:
   
   ```
   http://sipetra.test/oauth/authorize?client_id=[019d6c50-42ab-72d2-a099-69a8e633f5af]&redirect_uri=http://localhost:8001/callback&response_type=code&scope=profile:read identity:read
   ```

3. Browser akan mengarahkan Anda ke Halaman Login SIPETRA. 
4. Silakan login sebagai `pegawai` (NIP/Password) atau `mitra` (Email/Password).

---

## Langkah 3: Halaman Consent (Penyerahan Izin)

1. Sesudah berhasil login, jika klien ini belum pernah diizinkan sebelumnya, Anda akan melihat halaman "Authorize / Izin Akses" (Halaman *Consent* Passport).
2. SIPETRA akan menyatakan: *"Test SSO Client is requesting permission to access your account"*, beserta lingkup hak akses (scopes) yang dia minta.
3. Klik tombol **Authorize** (Izinkan).

---

## Langkah 4: Tangkap Authorization Code

1. Sipetra otomatis mengalihkan *redirect* URL Anda ke alamat `http://localhost:8001/callback?code=DEF5020...`
2. Walaupun Anda mungkin mendapatkan respon `Site Not Reached` (karena aplikasi localhost:8001 memang belum ada), **Ini Berhasil!** Perhatikan **Address Bar URL** browser Anda. 
3. Kopi (copy) nilai `code=...` yang panjang tersebut. Ini adalah **Authorization Code**.

---

## Langkah 5: Tukarkan Code dengan Access Token (via Postman / cURL)

Ini merupakan langkah yang *seharusnya* dilakukan secara otomatis oleh framework klien seperti Laravel Socialite. Kita simulasikan lewat cURL atau Postman.

Buat HTTP `POST` Request ke `http://localhost:8000/oauth/token`:

**Mode cURL:**
```bash
curl -X POST http://localhost:8000/oauth/token \
-H "Accept: application/json" \
-d "grant_type=authorization_code" \
-d "client_id=[CLIENT_ID]" \
-d "client_secret=[CLIENT_SECRET]" \
-d "redirect_uri=http://localhost:8001/callback" \
-d "code=[AUTHORIZATION_CODE_YANG_DICOOPY_TADI]"
```

Jika sukses, SIPETRA akan mengembalikan *Access Token*:
```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eX...",
  "refresh_token": "def50..."
}
```

---

## Langkah 6: Ambil Profil Data User

Gunakan `access_token` eksklusif yang baru Anda dapatkan untuk mengakses *Identity Endpoint* SIPETRA.

**Mode cURL:**
```bash
curl -X GET http://localhost:8000/api/user/identity \
-H "Accept: application/json" \
-H "Authorization: Bearer [ACCESS_TOKEN_ANDA]"
```

Anda akan menerima profil terekspos lengkap JSON (Sesuai Scopes yang diminta awal tadi):
```json
{
    "identity_type": "pegawai",
    "nip": "123456789012345678",
    "nip_baru": "...",
    "jenis_kelamin": "L",
    "tempat_lahir": "Jakarta"
}
```
*Demikianlah Alur SSO Oauth2 bekerja secara nyata!*
