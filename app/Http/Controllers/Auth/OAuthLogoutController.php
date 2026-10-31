<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OAuthLogoutController extends Controller
{
    /**
     * Handle Federated Single Logout (SLO).
     *
     * URL: GET /oauth/logout?redirect_uri=...
     * Atau: POST /logout, GET /logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $rawRedirectUri = $request->query('redirect_uri', $request->input('redirect_uri'));

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if ($user) {
                // Cabut access tokens aktif milik user ini
                DB::table('oauth_access_tokens')
                    ->where('user_id', $user->getAuthIdentifier())
                    ->where('revoked', false)
                    ->update(['revoked' => true]);
            }

            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Validasi redirect_uri terhadap daftar client terdaftar
        if (! empty($rawRedirectUri) && is_string($rawRedirectUri)) {
            $validatedUrl = $this->validateRedirectUri($rawRedirectUri);

            if ($validatedUrl !== null) {
                return redirect()->away($validatedUrl);
            }
        }

        return redirect()->route('login');
    }

    /**
     * Memvalidasi apakah URL tujuan redirect_uri cocok dengan salah satu client OAuth yang terdaftar.
     * Mencegah celah keamanan Open Redirect.
     */
    protected function validateRedirectUri(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $targetHost = parse_url($url, PHP_URL_HOST);
        $targetScheme = parse_url($url, PHP_URL_SCHEME);
        $targetPort = parse_url($url, PHP_URL_PORT);

        if (empty($targetHost) || ! in_array($targetScheme, ['http', 'https'], true)) {
            return null;
        }

        // Ambil semua redirect_uris dari client yang aktif
        $clientUrisList = DB::table('oauth_clients')
            ->where('revoked', false)
            ->pluck('redirect_uris');

        foreach ($clientUrisList as $jsonUris) {
            $uris = is_string($jsonUris) ? json_decode($jsonUris, true) : (array) $jsonUris;

            if (! is_array($uris)) {
                continue;
            }

            foreach ($uris as $registeredUri) {
                if (! is_string($registeredUri) || ! filter_var($registeredUri, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $regHost = parse_url($registeredUri, PHP_URL_HOST);
                $regScheme = parse_url($registeredUri, PHP_URL_SCHEME);
                $regPort = parse_url($registeredUri, PHP_URL_PORT);

                // Periksa kesamaan skema, host, dan port
                if (strcasecmp((string) $targetHost, (string) $regHost) === 0 &&
                    strcasecmp((string) $targetScheme, (string) $regScheme) === 0 &&
                    $targetPort === $regPort) {
                    return $url;
                }
            }
        }

        return null;
    }
}
