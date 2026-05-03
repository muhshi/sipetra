<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Transformasi data User ke format standar Master Data API.
 *
 * Format ini digunakan oleh endpoint GET /api/master/users
 * dan dikonsumsi oleh aplikasi-aplikasi client (ManajemenSurat, dll).
 *
 * @see docs/openapi.yaml — schema MasterUser
 */
class MasterUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Identifier unik — gunakan sebagai foreign key di sisi client
            'sipetra_id'     => (string) $this->id,

            // Data dasar
            'name'           => $this->name,
            'email'          => $this->email,

            // Foto profil — dikembalikan sebagai full URL absolut.
            // Client cukup simpan URL ini; tidak perlu download binary.
            // null jika user belum mengupload foto.
            'avatar_url'     => $this->resolveAvatarUrl(),

            // Identifikasi
            'identity_type'  => $this->identity_type instanceof \App\Enums\IdentityType
                                    ? $this->identity_type->value
                                    : $this->identity_type,
            'nip'            => $this->nip,
            'nip_baru'       => $this->nip_baru,
            'sobat_id'       => $this->sobat_id,

            // Kontak
            'nomor_hp'       => $this->phone,

            // Organisasi
            'jabatan'        => $this->jabatan,
            'golongan'       => $this->golongan,
            'unit_kerja'     => $this->unit_kerja,
            'kd_satker'      => $this->kd_satker,

            // Demografis
            'gender'         => $this->jenis_kelamin,

            // Status — field kunci untuk mitigasi pensiun/kontrak habis
            'is_active'      => (bool) $this->is_active,

            // Siklus mitra — null untuk pegawai
            'period'         => $this->period ?? null,
            'contract_start' => $this->contract_start?->format('Y-m-d'),
            'contract_end'   => $this->contract_end?->format('Y-m-d'),

            // Timestamp untuk incremental sync
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Resolve URL foto profil menjadi URL absolut yang dapat diakses publik.
     * Menangani kasus: path relatif storage, URL absolut, atau null.
     */
    private function resolveAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        // Jika sudah berupa URL absolut (dari SSO luar, gravatar, dll), kembalikan langsung
        if (filter_var($this->avatar_url, FILTER_VALIDATE_URL)) {
            return $this->avatar_url;
        }

        // Path relatif — generate full URL via Storage
        return Storage::disk('public')->url($this->avatar_url);
    }
}
