<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memvalidasi bahwa request Master Data API
 * menggunakan token dengan nama 'master-data-api'.
 *
 * Token ini di-generate oleh admin Sipetra via Tinker:
 *   $user->createToken('master-data-api')->accessToken
 *
 * Cara penggunaan di routes:
 *   Route::middleware(['auth:api', 'master.token'])->group(...)
 */
class ValidateMasterToken
{
    /**
     * Nama token yang diizinkan mengakses Master Data API.
     */
    private const ALLOWED_TOKEN_NAMES = [
        'master-data-api',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->token();

        if (! $token) {
            return response()->json(
                ['message' => 'Token tidak ditemukan.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Validasi nama token — hanya token bernama 'master-data-api' yang diizinkan
        if (! in_array($token->name, self::ALLOWED_TOKEN_NAMES, true)) {
            return response()->json(
                ['message' => 'Token ini tidak diizinkan mengakses Master Data API. Gunakan token bernama "master-data-api".'],
                Response::HTTP_FORBIDDEN
            );
        }

        return $next($request);
    }
}
