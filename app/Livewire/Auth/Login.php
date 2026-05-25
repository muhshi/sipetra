<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Filament\FilamentManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Login SSO - SIPETRA BPS')]
class Login extends Component
{
    public $username = '';

    public $password = '';

    public $remember = false;

    protected $rules = [
        'username' => 'required|string',
        'password' => 'required|string',
    ];

    public function authenticate()
    {
        $this->validate();

        $loginKey = trim($this->username);

        // Cari user berdasarkan NIP, NIP Baru, Email, atau awalan Email BPS
        $user = User::where(function ($query) use ($loginKey) {
            $query->where('nip', $loginKey)
                ->orWhere('nip_baru', $loginKey)
                ->orWhere('email', $loginKey);

            if (! str_contains($loginKey, '@')) {
                $query->orWhere('email', $loginKey.'@bps.go.id');
            }
        })->first();

        if (! $user) {
            $this->addError('username', 'Kredensial yang Anda berikan salah atau akun tidak aktif.');

            return;
        }

        $attempt = Auth::attempt([
            'email' => $user->email,
            'password' => $this->password,
            'is_active' => true,
        ], $this->remember);

        if ($attempt) {
            // Tangkap intended URL SEBELUM regenerasi sesi untuk mencegah kehilangan data
            $intendedUrl = session()->pull('url.intended', route('dashboard'));

            session()->regenerate();
            session()->save();

            Log::info('SSO Login berhasil', [
                'user' => $user->email,
                'intended_url' => $intendedUrl,
                'session_id' => session()->getId(),
                'session_driver' => config('session.driver'),
            ]);

            // Cek jika Pegawai biasa (bukan admin) tanpa sengaja memiliki memori 'intended' ke /admin,
            // (biasanya karena ia pernah mengklik link /admin sebelum login).
            // Maka kita paksa ia masuk ke /dashboard saja, daripada kena halaman 403 Forbidden.
            if (str_contains($intendedUrl, '/admin')) {
                /** @var User $currentUser */
                $currentUser = Auth::user();
                $panel = app(FilamentManager::class)->getCurrentPanel() ?? filament()->getPanel('admin');

                if (! $currentUser->canAccessPanel($panel)) {
                    return redirect()->route('dashboard');
                }
            }

            // Redirect langsung ke URL yang sudah ditangkap
            // return redirect($intendedUrl);
            dd([
                'STATUS' => 'LOGIN SUCCESS',
                'INTENDED_URL' => $intendedUrl,
                'SESSION_ID' => session()->getId(),
                'USER' => $user->email,
                'PLEASE_SEND_THIS_SCREENSHOT_TO_AI' => true
            ]);
        }

        $this->addError('username', 'Kredensial yang Anda berikan salah atau akun tidak aktif.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
