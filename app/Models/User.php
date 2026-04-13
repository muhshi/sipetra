<?php

namespace App\Models;

use App\Enums\IdentityType;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
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
class User extends Authenticatable implements FilamentUser, OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

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
