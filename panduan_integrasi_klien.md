# Panduan Penempelan SSO Sipetra ke Aplikasi Klien (Portal)

Dokumen ini berisi langkah-langkah praktis bagi pengembang (developer) aplikasi sektoral BPS atau Portal untuk menyambungkan aplikasinya dengan Server SSO Sipetra.

Secara teknis, Sipetra telah berjalan penuh menggunakan arsitektur **OAuth2**. Pilih salah satu skenario di bawah berdasarkan teknologi yang digunakan pada aplikasi Klien Anda.

---

## Skenario 1: Apabila Aplikasi Klien Dibangun Menggunakan Laravel

Klien berbasis Laravel sangat dimudahkan karena dapat digabungkan langsung dengan pustaka *Laravel Socialite*.

### 1. Dapatkan Kredensial Unik Aplikasi
Sebagai syarat masuk/komunikasi antar mesin, minta **Client ID** dan **Client Secret** khusus Portal/Sektoral tersebut dari Administrator Sipetra (bisa dipantau di tabel *oauth_clients* database Sipetra). 

Pasang kuncinya secara rahasia di file *Environment* lokal (`.env`) aplikasi Anda:
```env
SIPETRA_CLIENT_ID="[COPY_CLIENT_ID_DARI_SIPETRA]"
SIPETRA_CLIENT_SECRET="[COPY_CLIENT_SECRET_DARI_SIPETRA]"
SIPETRA_REDIRECT_URI="https://portal.bpsdemak.com/auth/callback"
```

### 2. Pasang Laravel Socialite
Instal pustaka OAuth2 bawaan laravel secara _remote_ melalui terminal Klien:
```bash
composer require laravel/socialite
```

### 3. Daftarkan Service di Config
Buka berkas `config/services.php` klien Anda, lalu tambahkan rujukan destinasi Sipetra:
```php
'sipetra' => [
    'client_id'     => env('SIPETRA_CLIENT_ID'),
    'client_secret' => env('SIPETRA_CLIENT_SECRET'),
    'redirect'      => env('SIPETRA_REDIRECT_URI'),
    'host'          => env('SIPETRA_HOST', 'https://sipetra.bpsdemak.com'), 
],
```

### 4. Inject Provider Kustom
Buat berkas *Provider* menjemput khusus (misal: `app/Providers/SipetraProvider.php`) yang menjembatani bahasa pemetaan profil BPS. (Detail instruksi baris kode *Provider* ini tersedia lengkap dalam dokumen PRD/Perencanaan Sipetra utama pada sub-bab *Client Integration*).

### 5. Pasang Route & Controller Interceptor
Pungut data pengguna di antara dua alur rute utama *Controller* Anda:
- **Rute Lempar (`/auth/redirect`)**: Ketika user menekan tombol 'Login dengan BPS'.
  *Kode:* `return Socialite::driver('sipetra')->redirect();`
- **Rute Tangkap (`/auth/callback`)**: Menjemput kembalian serah terima izin otentikasi dari Sipetra.
  *Kode:* `$user = Socialite::driver('sipetra')->user();` lalu eksekusi *login* secara programmatif (`Auth::login()`) agar user berhasil menduduki meja Dasbor Anda.

---

## Skenario 2: Apabila Aplikasi Klien Dibangun Teknologi Lain (Next.js, Vue, PHP Native)

Tidak masalah jika tidak berbasis Laravel. Aturan mainnya menaati protokol komunikasi standar HTTP / global OAuth2 yang terdiri dari 3 siklus *Endpoint* URL resmi:

### 1. Endpoint Lemparan Otentikasi
Tombol "Log in BPS" pada antarmuka Portal/Front-End Anda harus mengarahkan (_redirect_) layar _browser_ murni ke format URL berikut:
```text
https://sipetra.bpsdemak.com/oauth/authorize?client_id=[ID_PORTAL_ANDA]&redirect_uri=[URL_CALLBACK_ANDA]&response_type=code
```

### 2. Endpoint Pertukaran Tiket Token (*Background POST*)
Setelah *user* sah mengetik _password_-nya di Sipetra, Sipetra akan melempar *user* kembali ke `[URL_CALLBACK_ANDA]` sambil menempelkan sebuah _query parameter_ berupa `?code=xyza...`. 

Sistem *Backend* Klien Anda harus lekas menyambar parameter rahasia tersebut lalu mengirim _POST Request_ (di balik layar) menuju Sipetra untuk ditukar menjadi token akses sakti:
```json
POST https://sipetra.bpsdemak.com/oauth/token
{
    "grant_type": "authorization_code",
    "client_id": "[ID_PORTAL_ANDA]",
    "client_secret": "[RAHASIA_PORTAL_ANDA]",
    "redirect_uri": "[URL_CALLBACK_ANDA]",
    "code": "xyza..."
}
```
Sipetra akan membalas _request JSON_ tersebut dengan sebongkah data **`access_token`**.

### 3. Endpoint Suplai Ekstraksi Biodata
Bermodalkan `access_token` hasil penukaran tersebut, panggil API profil internal Sipetra untuk "menyedot" data penggunanya secara *real-time*:
```text
GET https://sipetra.bpsdemak.com/api/user
Header: 
  Authorization: Bearer [access_token_yg_baru_didapat]
```
Hasil JSON bersihkan dari *Endpoint* ini mengantungi kelengkapan profil (Nama, Identitas Email, Kode NIP, dan Jabatan) Pegawai bersangkutan. Tinggal Anda lemparkan / cetak (*render*) info JSON ini melenggang masuk ke laman profil Dasbor aplikasi Anda!
