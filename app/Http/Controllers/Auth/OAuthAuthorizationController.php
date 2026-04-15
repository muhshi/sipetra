<?php

namespace App\Http\Controllers\Auth;

use App\Services\AccessRuleResolver;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class OAuthAuthorizationController extends PassportAuthorizationController
{
    public function __construct(
        AuthorizationServer $server,
        StatefulGuard $guard,
        ClientRepository $clients,
        protected AccessRuleResolver $resolver,
    ) {
        parent::__construct($server, $guard, $clients);
    }

    /**
     * Override authorize: cek akses via AccessRuleResolver sebelum flow berjalan.
     * Jika user tidak diizinkan → tampilkan halaman penolakan.
     */
    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
        AuthorizationViewResponse $viewResponse
    ): Response|AuthorizationViewResponse {
        // Pastikan user sudah login dulu
        if ($this->guard->guest()) {
            return parent::authorize($psrRequest, $request, $psrResponse, $viewResponse);
        }

        $user = $this->guard->user();
        $clientId = $psrRequest->getQueryParams()['client_id'] ?? null;

        if ($clientId) {
            $client = $this->clients->find($clientId);

            if ($client && ! $this->resolver->isAllowed($user, $client)) {
                return response()->view('auth.oauth-denied', [
                    'clientName' => $client->name,
                ], 403);
            }
        }

        return parent::authorize($psrRequest, $request, $psrResponse, $viewResponse);
    }
}
