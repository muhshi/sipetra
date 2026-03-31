<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensCan([
            'profile:read' => 'Baca informasi profil dasar (nama, email, avatar)',
            'identity:read' => 'Baca identitas (NIP/ID Mitra, tipe)',
            'organization:read' => 'Baca info organisasi (satker, unit kerja, jabatan)',
            'phone:read' => 'Baca nomor telepon',
            'email:read' => 'Baca alamat email',
            'user:manage' => 'Akses penuh manajemen user',
        ]);

        Passport::setDefaultScope(['profile:read']);

        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
