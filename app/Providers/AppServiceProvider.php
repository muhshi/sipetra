<?php

namespace App\Providers;

use App\Http\Controllers\Auth\OAuthAuthorizationController;
use App\Models\PassportClient;
use App\Policies\ClientPolicy;
use App\Settings\SystemSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Laravel\Passport\Passport;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\CreateClient;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\EditClient;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\ViewClient;

use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CreateClient::class,
            \App\Filament\Resources\ClientResource\Pages\CreateClient::class,
        );

        $this->app->bind(
            ViewClient::class,
            \App\Filament\Resources\ClientResource\Pages\ViewClient::class,
        );

        $this->app->bind(
            EditClient::class,
            \App\Filament\Resources\ClientResource\Pages\EditClient::class,
        );

        $this->app->bind(PassportAuthorizationController::class, OAuthAuthorizationController::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(PassportClient::class, ClientPolicy::class);

        Passport::useClientModel(PassportClient::class);

        Passport::authorizationView(function ($parameters) {
            // Jika skipsAuthorization() == false, user tidak terdaftar → tampilkan halaman penolakan
            // Jika skipsAuthorization() == true, alur tidak sampai ke view ini
            return view('oauth.rejected', [
                'client' => $parameters['client'] ?? null,
            ]);
        });

        Passport::tokensCan([
            'profile:read' => 'Baca informasi profil dasar (nama, email, avatar)',
            'identity:read' => 'Baca identitas (NIP/ID Mitra, tipe)',
            'organization:read' => 'Baca info organisasi (satker, unit kerja, jabatan)',
            'phone:read' => 'Baca nomor telepon',
            'email:read' => 'Baca alamat email',
            'user:manage' => 'Akses penuh manajemen user',
        ]);

        Passport::useClientModel(PassportClient::class);

        Passport::setDefaultScope(['profile:read']);

        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        try {
            if (Schema::hasTable('settings')) {
                $settings = app(SystemSettings::class);
                config(['session.lifetime' => $settings->session_lifetime]);
            }
        } catch (\Throwable $th) {
            // Ignore if settings migration hasn't run
        }
    }
}
