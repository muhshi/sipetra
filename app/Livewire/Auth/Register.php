<?php

namespace App\Livewire\Auth;

use App\Enums\IdentityType;
use App\Models\User;
use Filament\FilamentManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Daftar Akun - SIPETRA BPS')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $sobat_id = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $intendedUrl = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'sobat_id' => ['nullable', 'string', 'max:50', 'unique:users,sobat_id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar di sistem SIPETRA.',
            'sobat_id.unique' => 'SOBAT ID ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }

    public function mount()
    {
        // Pertahankan intended URL (misal dari alur OAuth web klien)
        $this->intendedUrl = (string) session('url.intended', '');

        if (Auth::check()) {
            return $this->handleSuccessfulRedirect();
        }
    }

    public function register()
    {
        if (Auth::check()) {
            return $this->handleSuccessfulRedirect();
        }

        $this->validate();

        $user = User::create([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'phone' => trim($this->phone) ?: null,
            'sobat_id' => trim($this->sobat_id) ?: null,
            'password' => Hash::make($this->password),
            'identity_type' => IdentityType::Mitra, // Default pendaftaran publik adalah Mitra
            'is_active' => true,
        ]);

        // Berikan role mitra default jika ada
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('mitra');
            } catch (\Throwable $e) {
                // Abaikan jika role belum di-seed
            }
        }

        // Login otomatis pengguna baru
        Auth::login($user);

        // Ambil intended url dari session jika ada
        if (empty($this->intendedUrl)) {
            $this->intendedUrl = (string) session()->pull('url.intended', route('dashboard'));
        } else {
            session()->forget('url.intended');
        }

        session()->regenerate();
        session()->save();

        Log::info('SSO Registrasi mandiri berhasil', [
            'user' => $user->email,
            'identity_type' => $user->identity_type,
            'intended_url' => $this->intendedUrl,
            'session_id' => session()->getId(),
        ]);

        return $this->handleSuccessfulRedirect();
    }

    protected function handleSuccessfulRedirect()
    {
        $target = ! empty($this->intendedUrl) ? $this->intendedUrl : route('dashboard');

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
        return view('livewire.auth.register');
    }
}
