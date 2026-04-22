<?php

namespace App\Models;

use App\Enums\IdentityType;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Contracts\OAuthenticatable;
use N3XT0R\LaravelPassportAuthorizationCore\Models\Concerns\HasPassportScopeGrantsInterface;
use N3XT0R\LaravelPassportAuthorizationCore\Models\Traits\HasApiTokensTrait;
use N3XT0R\LaravelPassportAuthorizationCore\Models\Traits\HasPassportScopeGrantsTrait;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'identity_type',
    'nip',
    'nip_baru',
    'sobat_id',
    'kd_satker',
    'jabatan',
    'unit_kerja',
    'golongan',
    'jenis_kelamin',
    'tempat_lahir',
    'tanggal_lahir',
    'pendidikan',
    'phone',
    'avatar_url',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasPassportScopeGrantsInterface, OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokensTrait, HasFactory, HasPassportScopeGrantsTrait, HasRoles, Notifiable;

    /**
     * Get the user's avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::disk('public')->url($this->avatar_url) : null;
    }

    /**
     * Dapatkan role user untuk klien SSO tertentu.
     */
    public function clientRoleFor(string $clientId): ?string
    {
        $access = ClientUserAccess::with('role')
            ->where('client_id', $clientId)
            ->where('user_id', $this->id)
            ->first();

        return $access?->role?->name;
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // SIPETRA bertindak sebagai SSO Terpusat.
        // Semua user aktif (termasuk Pegawai biasa & Mitra) HARUS DIIZINKAN masuk/numpang login.
        // Halaman/Menu di dalam panel akan disembunyikan otomatis oleh Filament Shield sesuai Role mereka.
        return $this->is_active;
    }

    /**
     * Get the employee profile relationship.
     */
    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    /**
     * Get the displayable masa kerja.
     */
    public function getMasaKerjaAttribute(): string
    {
        if (! $this->employeeProfile) {
            return '-';
        }

        return "{$this->employeeProfile->mk_tahun} Tahun {$this->employeeProfile->mk_bulan} Bulan";
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'identity_type' => IdentityType::class,
            'tanggal_lahir' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
