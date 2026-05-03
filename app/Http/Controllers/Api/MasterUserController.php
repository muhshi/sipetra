<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller untuk Master Data API — diakses oleh aplikasi client (M2M).
 *
 * Autentikasi: Bearer Token (Personal Access Token Passport).
 * Endpoint ini bukan untuk user biasa, melainkan untuk server-to-server sync.
 *
 * @see docs/openapi.yaml
 * @see docs/API_MASTER_USERS.md
 */
class MasterUserController extends Controller
{
    /**
     * GET /api/master/users
     *
     * Mengembalikan daftar semua pengguna (pegawai & mitra) dengan pagination.
     * Mendukung filter inkremental via `updated_after` untuk efisiensi sinkronisasi.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'type'          => ['nullable', 'string', 'in:pegawai,mitra,admin'],
            'period'        => ['nullable', 'string', 'max:50'],
            'updated_after' => ['nullable', 'date'],
            'per_page'      => ['nullable', 'integer', 'min:1', 'max:500'],
            'page'          => ['nullable', 'integer', 'min:1'],
        ]);

        $query = User::query()->orderBy('id');

        // Filter berdasarkan tipe identitas
        if ($request->filled('type')) {
            $query->where('identity_type', $request->input('type'));
        }

        // Filter berdasarkan periode mitra
        if ($request->filled('period')) {
            $query->where('period', $request->input('period'));
        }

        // Incremental sync — hanya data yang berubah setelah timestamp tertentu
        if ($request->filled('updated_after')) {
            $query->where('updated_at', '>', $request->input('updated_after'));
        }

        $perPage = (int) $request->input('per_page', 100);
        $users   = $query->paginate($perPage);

        // Tambahkan `synced_at` ke response meta — client harus simpan ini
        // sebagai `updated_after` untuk sync inkremental berikutnya.
        return MasterUserResource::collection($users)
            ->additional([
                'synced_at' => now()->toIso8601String(),
            ]);
    }

    /**
     * GET /api/master/users/{sipetra_id}
     *
     * Mengembalikan detail satu pengguna berdasarkan sipetra_id (= id di Sipetra).
     * Berguna untuk validasi real-time saat client butuh data terkini satu user.
     */
    public function show(string $sipetraId): MasterUserResource
    {
        $user = User::findOrFail($sipetraId);

        return new MasterUserResource($user);
    }
}
