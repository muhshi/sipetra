<?php

namespace App\Livewire\Account;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class UserProfileModal extends Component
{
    public bool $isOpen = false;

    public string $activeTab = 'profile';

    public bool $isEditingProfile = false;

    public ?string $profileSuccessMessage = null;

    public string $phone = '';

    public string $alamat = '';

    public string $tempat_lahir = '';

    public string $tanggal_lahir = '';

    public string $pendidikan = '';

    public string $nama_bank = '';

    public string $nomor_rekening = '';

    public string $nama_pemilik_rekening = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public ?string $passwordSuccessMessage = null;

    public ?string $sessionSuccessMessage = null;

    public function mount(): void
    {
        if (request()->has('open_profile') || request()->has('profile') || request()->boolean('open_profile')) {
            $this->isOpen = true;
            $this->activeTab = (string) request()->query('tab', 'profile');
        }
    }

    #[On('open-user-profile')]
    public function openModal(string $tab = 'profile'): void
    {
        $this->activeTab = $tab;
        $this->isOpen = true;
        $this->isEditingProfile = false;
        $this->profileSuccessMessage = null;
        $this->resetPasswordForm();
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->isEditingProfile = false;
        $this->profileSuccessMessage = null;
        $this->resetPasswordForm();
    }

    public function startEditingProfile(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->phone = (string) ($user->phone ?? '');
        $this->alamat = (string) ($user->alamat ?? '');
        $this->tempat_lahir = (string) ($user->tempat_lahir ?? '');
        $this->tanggal_lahir = $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '';
        $this->pendidikan = (string) ($user->pendidikan ?? '');
        $this->nama_bank = (string) ($user->nama_bank ?? '');
        $this->nomor_rekening = (string) ($user->nomor_rekening ?? '');
        $this->nama_pemilik_rekening = (string) ($user->nama_pemilik_rekening ?? '');

        $this->profileSuccessMessage = null;
        $this->isEditingProfile = true;
        $this->resetErrorBag();
    }

    public function cancelEditingProfile(): void
    {
        $this->isEditingProfile = false;
        $this->resetErrorBag();
    }

    public function saveProfile(): void
    {
        $this->validate([
            'phone' => ['nullable', 'string', 'max:25'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'pendidikan' => ['nullable', 'string', 'max:100'],
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'nama_pemilik_rekening' => ['nullable', 'string', 'max:255'],
        ], [
            'phone.max' => 'Nomor telepon maksimal 25 karakter.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'phone' => filled($this->phone) ? trim($this->phone) : null,
            'alamat' => filled($this->alamat) ? trim($this->alamat) : null,
            'tempat_lahir' => filled($this->tempat_lahir) ? trim($this->tempat_lahir) : null,
            'tanggal_lahir' => filled($this->tanggal_lahir) ? $this->tanggal_lahir : null,
            'pendidikan' => filled($this->pendidikan) ? trim($this->pendidikan) : null,
            'nama_bank' => filled($this->nama_bank) ? trim($this->nama_bank) : null,
            'nomor_rekening' => filled($this->nomor_rekening) ? trim($this->nomor_rekening) : null,
            'nama_pemilik_rekening' => filled($this->nama_pemilik_rekening) ? trim($this->nama_pemilik_rekening) : null,
        ]);

        $this->isEditingProfile = false;
        $this->profileSuccessMessage = 'Data profil berhasil diperbarui.';
    }

    public function resetPasswordForm(): void
    {
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->passwordSuccessMessage = null;
        $this->sessionSuccessMessage = null;
        $this->resetErrorBag();
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->passwordSuccessMessage = 'Password berhasil diperbarui.';
    }

    public function revokeSession(string $sessionId): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $isCurrent = $sessionId === session()->getId();

        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted && $isCurrent) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->redirectRoute('login');

            return;
        }

        $this->sessionSuccessMessage = 'Sesi perangkat berhasil diakhiri.';
    }

    public function revokeOtherSessions(): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $currentSessionId = session()->getId();

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        $this->sessionSuccessMessage = 'Semua sesi perangkat lain berhasil diputus.';
    }

    /**
     * Get active sessions for current user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSessionsProperty(): array
    {
        $userId = Auth::id();

        if (! $userId) {
            return [];
        }

        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get();

        $currentId = session()->getId();

        return $sessions->map(function ($s) use ($currentId) {
            $agent = (string) $s->user_agent;

            return [
                'id' => $s->id,
                'is_current' => $s->id === $currentId,
                'ip_address' => $s->ip_address,
                'device' => $this->parseDevice($agent),
                'browser' => $this->parseBrowser($agent),
                'last_active' => Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
            ];
        })->all();
    }

    protected function parseDevice(string $agent): string
    {
        if (preg_match('/(iPhone|iPad|iPod)/i', $agent)) {
            return 'Apple iOS';
        }
        if (preg_match('/Android/i', $agent)) {
            return 'Android Device';
        }
        if (preg_match('/Macintosh|Mac OS X/i', $agent)) {
            return 'Apple Mac';
        }
        if (preg_match('/Windows/i', $agent)) {
            return 'Windows PC';
        }
        if (preg_match('/Linux/i', $agent)) {
            return 'Linux';
        }

        return 'Perangkat Lain';
    }

    protected function parseBrowser(string $agent): string
    {
        if (preg_match('/Edg/i', $agent)) {
            return 'Microsoft Edge';
        }
        if (preg_match('/Chrome/i', $agent)) {
            return 'Google Chrome';
        }
        if (preg_match('/Firefox/i', $agent)) {
            return 'Mozilla Firefox';
        }
        if (preg_match('/Safari/i', $agent)) {
            return 'Apple Safari';
        }

        return 'Web Browser';
    }

    public function render(): View
    {
        return view('livewire.account.user-profile-modal', [
            'user' => Auth::user(),
            'sessions' => $this->sessions,
        ]);
    }
}
