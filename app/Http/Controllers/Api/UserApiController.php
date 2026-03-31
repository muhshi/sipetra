<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * GET /api/user — profil dasar user yang ter-autentikasi.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar_url,
        ]);
    }

    /**
     * GET /api/user/identity — data identitas (NIP/SOBAT ID, tipe).
     */
    public function identity(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'identity_type' => $user->identity_type,
            'nip' => $user->nip,
            'nip_baru' => $user->nip_baru,
            'sobat_id' => $user->sobat_id,
            'jenis_kelamin' => $user->jenis_kelamin,
            'tempat_lahir' => $user->tempat_lahir,
            'tanggal_lahir' => $user->tanggal_lahir?->format('Y-m-d'),
            'pendidikan' => $user->pendidikan,
        ]);
    }

    /**
     * GET /api/user/organization — data organisasi.
     */
    public function organization(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'kd_satker' => $user->kd_satker,
            'unit_kerja' => $user->unit_kerja,
            'jabatan' => $user->jabatan,
            'golongan' => $user->golongan,
        ]);
    }
}
