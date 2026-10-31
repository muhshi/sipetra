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

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public ?string $passwordSuccessMessage = null;

    public ?string $sessionSuccessMessage = null;

    #[On('open-user-profile')]
    public function openModal(string $tab = 'profile'): void
    {
        $this->activeTab = $tab;
        $this->isOpen = true;
        $this->resetPasswordForm();
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetPasswordForm();
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
