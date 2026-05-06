<?php

namespace App\Livewire\Auth;

use App\Enums\IdentityType;
use App\Models\User;
use Filament\FilamentManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Login SSO - SIPETRA BPS')]
class Login extends Component
{
    public $loginType = 'nip'; // Default login for employees

    public $nip = '';

    public $email = '';

    public $password = '';

    public $remember = false;

    protected $rules = [
        'password' => 'required',
    ];

    public function updatedLoginType()
    {
        $this->resetValidation();
        $this->nip = '';
        $this->email = '';
    }

    public function authenticate()
    {
        if ($this->loginType === 'nip') {
            $this->validate([
                'nip' => 'required|string',
                'password' => 'required|string',
            ]);

            // Try with NIP Lama first
            $attempt = Auth::attempt(['nip' => $this->nip, 'password' => $this->password, 'is_active' => true], $this->remember);

            // If failed, try with NIP Baru
            if (! $attempt) {
                $attempt = Auth::attempt(['nip_baru' => $this->nip, 'password' => $this->password, 'is_active' => true], $this->remember);
            }

        } else {
            $this->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $this->email)->first();


            $attempt = Auth::attempt(['email' => $this->email, 'password' => $this->password, 'is_active' => true], $this->remember);
        }

        if ($attempt) {
            session()->regenerate();

            // Get intended URL
            $intended = redirect()->getIntendedUrl();

            // Cek jika Pegawai biasa (bukan admin) tanpa sengaja memiliki memori 'intended' ke /admin,
            // (biasanya karena ia pernah mengklik link /admin sebelum login).
            // Maka kita paksa ia masuk ke /dashboard saja, daripada kena halaman 403 Forbidden.
            if ($intended && str_contains($intended, '/admin')) {
                /** @var User $user */
                $user = Auth::user();
                $panel = app(FilamentManager::class)->getCurrentPanel() ?? filament()->getPanel('admin');

                if (! $user->canAccessPanel($panel)) {
                    return redirect('/admin');
                }
            }

            // Intended redirect for Passport/SSO atau Dashboard fallback
            return redirect()->intended('/admin');
        }

        $this->addError('password', 'Kredensial yang Anda berikan salah atau akun tidak aktif.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
