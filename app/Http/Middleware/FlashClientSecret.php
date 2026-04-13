<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FlashClientSecret
{
    /**
     * Check for newly created OAuth client secrets in the session
     * and display them as persistent Filament notifications.
     *
     * The filament-passport-ui plugin stores secrets via
     * Session::put('new_client_secret_{id}') after creation,
     * but never surfaces them to the user in the UI.
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach ($request->session()->all() as $key => $value) {
            if (! str_starts_with((string) $key, 'new_client_secret_')) {
                continue;
            }

            Notification::make()
                ->success()
                ->title(__('OAuth Client Created'))
                ->body(
                    __('Your client secret is:')."\n\n"
                    .$value."\n\n"
                    .__('⚠️ Copy this now — it will not be shown again.')
                )
                ->persistent()
                ->send();

            $request->session()->forget($key);
        }

        return $next($request);
    }
}
