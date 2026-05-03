<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memvalidasi bahwa request Master Data API
 * menggunakan Personal Access Token yang valid (M2M / server-to-server).
 *
 * Middleware ini TIDAK menggantikan auth:api — ia berjalan setelahnya
 * sebagai lapisan validasi tambahan untuk memastikan token bukan token
 * user biasa (OAuth code flow), melainkan token M2M yang disetujui admin.
 *
 * Cara penggunaan di routes:
 *   Route::middleware(['auth:api', 'master.token'])->group(...)
 */
class ValidateMasterToken
{
    /**
     * Nama token yang diizinkan mengakses Master Data API.
     * Saat admin generate token via Passport, wajib gunakan salah satu nama ini.
     */
    private const ALLOWED_TOKEN_NAMES = [
        'master-data-api',     // nama standar — gunakan ini saat generate token
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user  = $request->user();
        $token = $user?->token();

        if (! $token) {
            return response()->json([
                'message' => 'Token tidak ditemukan.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Pastikan token adalah Personal Access Token (bukan Authorization Code)
        if (! $token->personal_access_client) {
            return response()->json([
                'message' => 'Endpoint ini hanya dapat diakses menggunakan Personal Access Token (M2M).',
            ], Response::HTTP_FORBIDDEN);
        }

        // Validasi nama token agar hanya token yang diizinkan admin yang bisa akses
        if (! in_array($token->name, self::ALLOWED_TOKEN_NAMES, true)) {
            return response()->json([
                'message' => 'Token tidak diizinkan mengakses Master Data API. Hubungi administrator.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
