# Panduan Integrasi SIPETRA SSO (OAuth2)

Dokumen ini berisi langkah-langkah teknis untuk mengintegrasikan sistem autentikasi **SIPETRA SSO** (Single Sign-On) ke dalam aplikasi Laravel menggunakan **Laravel Socialite**.

## 1. Konfigurasi Environment (`.env`)

Tambahkan variabel berikut di file `.env` aplikasi klien:

```env
SIPETRA_CLIENT_ID="isi_dengan_client_id_dari_sipetra"
SIPETRA_CLIENT_SECRET="isi_dengan_client_secret_dari_sipetra"
SIPETRA_REDIRECT_URI="${APP_URL}/auth/sipetra/callback"
SIPETRA_BASE_URL="https://bpsdemak.com"
```

## 2. Registrasi Service (`config/services.php`)

Daftarkan kredensial SIPETRA di dalam array `config/services.php`:

```php
'sipetra' => [
    'client_id' => env('SIPETRA_CLIENT_ID'),
    'client_secret' => env('SIPETRA_CLIENT_SECRET'),
    'redirect' => env('SIPETRA_REDIRECT_URI'),
    'base_url' => env('SIPETRA_BASE_URL'),
    // Scope wajib untuk mendapatkan data profil
    'scopes' => ['identity_pegawai:read', 'employee:read', 'contact:read', 'roles:read'],
],
```

## 3. Custom Socialite Provider

Buat file `app/Providers/SipetraSocialiteProvider.php` untuk menangani protokol OAuth2 SIPETRA:

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
        $response = $this->getHttpClient()->get(
            config('services.sipetra.base_url') . '/api/user',
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
        return config('services.sipetra.scopes', []);
    }
}
```

Daftarkan provider tersebut di `app/Providers/AppServiceProvider.php` pada method `boot()`:

```php
public function boot(): void
{
    $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);
    $socialite->extend('sipetra', function ($app) use ($socialite) {
        $config = $app['config']['services.sipetra'];
        return $socialite->buildProvider(\App\Providers\SipetraSocialiteProvider::class, $config);
    });
}
```

## 4. Database & Model User

Pastikan tabel `users` memiliki kolom untuk menyimpan ID SSO dan data tambahan. Jalankan migrasi:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('sipetra_id')->nullable()->unique();
    $table->text('sipetra_token')->nullable();
    $table->text('sipetra_refresh_token')->nullable();
    $table->string('nip')->nullable();
    $table->string('jabatan')->nullable();
    // kolom lain sesuai kebutuhan
});
```

Pastikan kolom-kolom tersebut masuk ke `$fillable` di model `User.php`.

## 5. Route & Controller SSO

Buat controller `app/Http/Controllers/Auth/SsoController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('sipetra')->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Login SSO Dibatalkan');
        }

        try {
            $ssoUser = Socialite::driver('sipetra')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Gagal mengambil data user');
        }

        $rawData = $ssoUser->getRaw();

        // Cari user berdasarkan sipetra_id atau email
        $user = User::where('sipetra_id', $ssoUser->getId())->first()
             ?? User::where('email', $ssoUser->getEmail())->first();

        $data = [
            'sipetra_id'    => $ssoUser->getId(),
            'name'          => $ssoUser->getName(),
            'email'         => $ssoUser->getEmail(),
            'sipetra_token' => $ssoUser->token,
            'nip'           => $rawData['nip'] ?? null,
            // mapping data lain dari $rawData
        ];

        if ($user) {
            $user->update($data);
        } else {
            $data['password'] = null;
            $user = User::create($data);
            
            // Assign role default (jika pakai Spatie Permission)
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('pegawai');
            }
        }

        Auth::login($user);
        return redirect()->intended('/admin');
    }
}
```

Tambahkan route di `routes/web.php`:

```php
Route::get('/auth/sipetra/redirect', [SsoController::class, 'redirect'])->name('sipetra.login');
Route::get('/auth/sipetra/callback', [SsoController::class, 'callback']);
```

## 6. Integrasi UI (Filament)

Jika menggunakan Filament, tambahkan tombol login di `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->renderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            fn (): string => Blade::render('
                <div class="mt-4">
                    <x-filament::button
                        href="{{ route(\'sipetra.login\') }}"
                        tag="a"
                        color="info"
                        outlined
                        full-width
                    >
                        Masuk dengan SIPETRA SSO
                    </x-filament::button>
                </div>
            '),
        );
}
```

## Catatan Penting
- **Scopes**: Pastikan scope yang diminta di `config/services.php` sudah di-assign ke client di panel admin SIPETRA.
- **HTTPS**: Di lingkungan production, pastikan `APP_URL` menggunakan `https://` agar callback tidak error.
