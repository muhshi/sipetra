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

    public $intendedUrl = '';

    protected $rules = [
        'username' => 'required|string',
        'password' => 'required|string',
    ];

    public function mount()
    {
        // Tangkap intended URL saat komponen pertama kali dimount agar tidak hilang saat reload atau double click
        $this->intendedUrl = (string) session('url.intended', '');

        // Jika user sudah terautentikasi saat membuka halaman login, langsung teruskan
        if (Auth::check()) {
            return $this->handleSuccessfulRedirect();
        }
    }

    public function authenticate()
    {
        // 1. Jika sudah terautentikasi (misal klik kedua yang terkirim saat request pertama sedang diproses)
        if (Auth::check()) {
            return $this->handleSuccessfulRedirect();
        }

        $this->validate();

        $loginKey = trim($this->username);

        // Cari user berdasarkan NIP, NIP Baru, SOBAT ID, Email, atau awalan Email BPS
        $user = User::where(function ($query) use ($loginKey) {
            $query->where('nip', $loginKey)
                ->orWhere('nip_baru', $loginKey)
                ->orWhere('sobat_id', $loginKey)
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
            // Prioritas intended URL dari state property komponen, fallback ke session jika ada
            if (empty($this->intendedUrl)) {
                $this->intendedUrl = (string) session()->pull('url.intended', route('dashboard'));
            } else {
                session()->forget('url.intended');
            }

            session()->regenerate();
            session()->save();

            Log::info('SSO Login berhasil', [
                'user' => $user->email,
                'intended_url' => $this->intendedUrl,
                'session_id' => session()->getId(),
                'session_driver' => config('session.driver'),
            ]);

            return $this->handleSuccessfulRedirect();
        }

        $this->addError('username', 'Kredensial yang Anda berikan salah atau akun tidak aktif.');
    }

    protected function handleSuccessfulRedirect()
    {
        $target = ! empty($this->intendedUrl) ? $this->intendedUrl : route('dashboard');

        // Cek jika Pegawai biasa (bukan admin) tanpa sengaja memiliki memori 'intended' ke /admin,
        // (biasanya karena ia pernah mengklik link /admin sebelum login).
        // Maka kita arahkan ke beranda portal saja, daripada kena halaman 403 Forbidden.
        if (str_contains($target, '/admin')) {
            $currentUser = Auth::user();
            $panel = app(FilamentManager::class)->getCurrentPanel() ?? filament()->getPanel('admin');

            if (! $currentUser || ! $currentUser->canAccessPanel($panel)) {
                $target = route('home');
            }
        }

        return redirect($target);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
