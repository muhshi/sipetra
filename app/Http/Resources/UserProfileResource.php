<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar_url,
            'client_role' => null,
            'profile' => [
                'identity_type' => $this->identity_type?->value ?? $this->identity_type,
                'nip' => $this->nip,
                'nip_baru' => $this->nip_baru,
                'sobat_id' => $this->sobat_id,
                'jenis_kelamin' => $this->jenis_kelamin,
                'tempat_lahir' => $this->tempat_lahir,
                'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
                'pendidikan' => $this->pendidikan,
            ],
            'organization' => [
                'kd_satker' => $this->kd_satker,
                'unit_kerja' => $this->unit_kerja,
                'jabatan' => $this->jabatan,
                'golongan' => $this->golongan,
            ],
        ];
    }
}
