<?php

namespace App\Http\Controllers\Auth;

use App\Models\Passport\Client;
use App\Services\AccessRuleResolver;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Http\Controllers\AuthorizationController as BaseAuthorizationController;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class OAuthAuthorizationController extends BaseAuthorizationController
{
    public function __construct(
        AuthorizationServer $server,
        AuthManager $auth,
        ClientRepository $clients,
        protected AccessRuleResolver $accessRuleResolver,
    ) {
        parent::__construct($server, $auth->guard(config('passport.guard')), $clients);
    }

    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
        AuthorizationViewResponse $viewResponse
    ): Response|AuthorizationViewResponse {
        $authRequest = $this->withErrorHandling(
            fn (): AuthorizationRequestInterface => $this->server->validateAuthorizationRequest($psrRequest),
            ($psrRequest->getQueryParams()['response_type'] ?? null) === 'token'
        );

        if ($this->guard->guest()) {
            $request->input('prompt') === 'none'
                ? throw \Laravel\Passport\Exceptions\OAuthServerException::loginRequired($authRequest)
                : $this->promptForLogin($request);
        }

        if ($request->input('prompt') === 'login' &&
            ! $request->session()->get('promptedForLogin', false)) {
            $this->guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->promptForLogin($request);
        }

        $request->session()->forget('promptedForLogin');

        $user = $this->guard->user();
        $client = $this->clients->find($authRequest->getClient()->getIdentifier());

        if ($client instanceof Client && ! $this->accessRuleResolver->isAllowed($user, $client)) {
            return response(
                $this->buildDeniedHtml($client->name, $user->name),
                Response::HTTP_FORBIDDEN
            );
        }

        $authRequest->setUser(new \Laravel\Passport\Bridge\User($user->getAuthIdentifier()));

        $scopes = $this->parseScopes($authRequest);

        if ($request->input('prompt') !== 'consent' &&
            ($client->skipsAuthorization($user, $scopes) || $this->hasGrantedScopes($user, $client, $scopes))) {
            return $this->approveRequest($authRequest, $psrResponse);
        }

        if ($request->input('prompt') === 'none') {
            throw \Laravel\Passport\Exceptions\OAuthServerException::consentRequired($authRequest);
        }

        $request->session()->put('authToken', $authToken = \Illuminate\Support\Str::random());
        $request->session()->put('authRequest', serialize($authRequest));

        return $viewResponse->withParameters([
            'client' => $client,
            'user' => $user,
            'scopes' => $scopes,
            'request' => $request,
            'authToken' => $authToken,
        ]);
    }

    private function buildDeniedHtml(string $clientName, string $userName): string
    {
        $dashboardUrl = route('dashboard');
        $logoutUrl = route('logout');
        $csrfToken = csrf_token();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
</head>
<body style="margin:0;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif;">
    <div style="max-width:760px;margin:80px auto;padding:24px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:40px;box-shadow:0 10px 30px rgba(15,23,42,.06);">
            <p style="margin:0 0 12px;color:#dc2626;font-size:12px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;">OAuth Access Denied</p>
            <h1 style="margin:0 0 16px;font-size:32px;line-height:1.2;">Aplikasi ini belum mengizinkan akun Anda.</h1>
            <p style="margin:0 0 8px;font-size:16px;line-height:1.7;color:#475569;">{$userName} tidak memiliki rule akses yang cocok untuk client <strong>{$clientName}</strong>.</p>
            <p style="margin:0 0 28px;font-size:14px;line-height:1.7;color:#64748b;">Silakan hubungi admin SIPETRA atau pemilik aplikasi untuk meminta akses.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="{$dashboardUrl}" style="display:inline-block;background:#0f172a;color:#fff;text-decoration:none;padding:14px 18px;border-radius:14px;font-weight:700;">Kembali ke Dashboard</a>
                <form action="{$logoutUrl}" method="POST" style="margin:0;">
                    <input type="hidden" name="_token" value="{$csrfToken}">
                    <button type="submit" style="background:#fff;color:#334155;border:1px solid #cbd5e1;padding:14px 18px;border-radius:14px;font-weight:700;cursor:pointer;">Logout</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
