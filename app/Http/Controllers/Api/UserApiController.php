<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserApiController extends Controller
{
    /**
     * GET /api/user — profil dasar user yang ter-autentikasi.
     */
    /**
     * GET /api/user — Data profil user dinamis berdasarkan scope token.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->token();
        $clientId = $token->client_id;

        // Data profil dasar (selalu dikembalikan tanpa cek scope)
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar_url ? Storage::disk('public')->url($user->avatar_url) : null,
            'identity_type' => $user->identity_type,
        ];

        // Scope Identity Pegawai
        if ($token->can('identity_pegawai:read')) {
            $data['nip'] = $user->nip;
            $data['nip_baru'] = $user->nip_baru;
        }

        // Scope Identity Mitra
        if ($token->can('identity_mitra:read')) {
            $data['sobat_id'] = $user->sobat_id;
            $data['nik'] = $user->nik ?? null; // Jika ada field nik nantinya
        }

        // Scope Kontak
        if ($token->can('contact:read')) {
            $data['phone_number'] = $user->no_hp ?? null; // Sesuaikan dengan kolom nomor HP di DB
            $data['address'] = $user->alamat ?? null; // Sesuaikan dengan kolom alamat di DB
        }

        // Scope Data Pegawai Khusus (Employee)
        if ($token->can('employee:read')) {
            if ($user->identity_type === \App\Enums\IdentityType::Pegawai) {
                // Pastikan load relasi jika dibutuhkan
                $user->loadMissing('employeeProfile');
                $profile = $user->employeeProfile;

                $data['employee'] = [
                    'kd_satker' => $user->kd_satker,
                    'unit_kerja' => $user->unit_kerja,
                    'jabatan' => $user->jabatan,
                    'golongan' => $user->golongan,
                    'tmt_cpns' => $profile?->tmt_cpns?->format('Y-m-d'),
                    'tmt_pns' => $profile?->tmt_pns?->format('Y-m-d'),
                    'tmt_golongan' => $profile?->tmt_golongan?->format('Y-m-d'),
                    'tmt_jabatan' => $profile?->tmt_jabatan?->format('Y-m-d'),
                    'masa_kerja_tahun' => $profile?->masa_kerja_tahun,
                    'masa_kerja_bulan' => $profile?->masa_kerja_bulan,
                    'agama' => $profile?->agama,
                    'status_aktif' => $profile?->status_aktif,
                ];
            } else {
                $data['employee'] = null;
            }
        }

        // Scope Roles
        if ($token->can('roles:read')) {
            $data['client_role'] = $user->clientRoleFor($clientId);
            $data['system_roles'] = $user->getRoleNames();
        }

        return response()->json($data);
    }
}
