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
            return view('passport::authorize', $parameters);
        });

        Passport::tokensCan([
            'profile:read' => 'Baca informasi profil dasar (nama, email, avatar, tipe user)',
            'identity_pegawai:read' => 'Baca identitas khusus pegawai (NIP Baru/Lama)',
            'identity_mitra:read' => 'Baca identitas khusus mitra (SOBAT ID, NIK)',
            'employee:read' => 'Baca data detil kepegawaian (Jabatan, Golongan, Masa Kerja, Agama, dsb)',
            'contact:read' => 'Baca data kontak (Nomor HP, Alamat)',
            'roles:read' => 'Baca hak akses (Roles & Permissions)',
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
