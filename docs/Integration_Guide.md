# Panduan Integrasi SSO SIPETRA ke Aplikasi Klien BPS

Materi ini diperuntukkan bagi *Developer* aplikasi turunan BPS yang ingin menggunakan SIPETRA *(Single Sign-On)*.
SIPETRA dirancang **bukan untuk merusak data User** di sistem Anda, melainkan hanya sebagai **"Gerbang Konfirmasi Identitas"**. 

Jika pengguna Anda sudah terdaftar di aplikasi Anda, SSO SIPETRA **akan mendeteksi dan secara mulus menyambungkan *(link)*** sesi mereka tanpa harus membuat duplikat akun baru.

---

## 1. Persiapan Klien & Package
Pastikan Anda mendaftarkan aplikasi Anda ke Tim Administrator SIPETRA agar mendapatkan `Client ID` dan `Client Secret`.
Di aplikasi Anda, install modul Socialite:
```bash
composer require laravel/socialite
```

### Tambahkan Provider Custom di `AppServiceProvider.php` (Aplikasi Klien)
Letakkan file `SipetraProvider.php` dari rilis kami ke dalam sistem Anda (Misal di `App\Providers\Socialite\SipetraProvider`). Lalu resmikan di skrip Provider:

```php
use Laravel\Socialite\Facades\Socialite;
use App\Providers\Socialite\SipetraProvider;

public function boot()
{
    Socialite::extend('sipetra', function ($app) {
        $config = $app['config']['services.sipetra'];
        return Socialite::buildProvider(SipetraProvider::class, $config);
    });
}
```

---

## 2. Pahami Skema Logika Login Anda (Controller SSO)

Berikut adalah **PRAKTIK TERBAIK (Best Practice)** pengolahan data pasca-login. Gunakan logika ini untuk menautkan profil existing Anda dengan kredensial SIPETRA.

```php
namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    // Rute masuk: redirect(/sso-login)
    public function redirect()
    {
        return Socialite::driver('sipetra')
            ->scopes(['profile:read', 'identity:read'])
            ->redirect();
    }

    // Rute kembali/Callback
    public function callback()
    {
        $ssoUser = Socialite::driver('sipetra')->user();

        // 💡 1. LINKING STRATEGY (Prioritas Utama)
        // Kita cari User di database APLIKASI KLIEN berdasarkan Email ATAU NIP yang sama
        $user = User::where('email', $ssoUser->email)
                    ->orWhere('nip', $ssoUser->user['nip'] ?? '')
                    ->first();

        // 💡 2. UPDATE ATAU BUAT BARU
        if ($user) {
            // Jika akun di tabel Anda sudah terdaftar (Misal pegawai lama yang baru pertama pakai SSO)
            // Anda boleh memperbarui (sync) info terbarunya di sini.
            $user->update([
                'name' => $ssoUser->name, // Selaraskan nama terbaru
                // Mungkin ada token penyimpanan
                'sipetra_token' => $ssoUser->token
            ]);
        } else {
            // Jika benar-benar akun baru dan belum ada di sistem aplikasi ini sama sekali
            $user = User::create([
                'name' => $ssoUser->name,
                'email' => $ssoUser->email,
                'nip' => $ssoUser->user['nip'],
                'password' => bcrypt(str_random(16)), // Sandi acak krn menggunakan SSO
            ]);
        }

        // 💡 3. PAKSA MASUK SESI
        Auth::login($user, true); // Login ke guard web utama aplikasi Anda
        return redirect()->intended('/dashboard-app-saya');
    }
}
```

### Mengapa Pendekatan Di Atas Aman?
Dengan pendekatan di atas, jika `andi@bps.go.id` sebelumnya sudah login biasa menggunakan kolom password tabel aplikasi Anda, dan hari ini dia tiba-tiba memencet tombol **Login SSO SIPETRA**, hasil saring `where('email', $ssoUser->email)` akan sukses membidiknya dan memasukkannya kembali ke profil aslinya! Data tidak akan tabrakan.
