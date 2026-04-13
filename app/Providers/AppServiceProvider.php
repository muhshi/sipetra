<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        Passport::authorizationView('vendor.passport.authorize');

        if (Schema::hasTable('passport_scope_actions') && Schema::hasTable('passport_scope_resources')) {
            $dbScopes = DB::table('passport_scope_actions')
                ->join('passport_scope_resources', 'passport_scope_actions.resource_id', '=', 'passport_scope_resources.id')
                ->select(
                    'passport_scope_resources.name as resource_name',
                    'passport_scope_actions.name as action_name',
                    'passport_scope_actions.description'
                )
                ->where('passport_scope_actions.is_active', true)
                ->get();

            $formattedScopes = [];
            foreach ($dbScopes as $scope) {
                $formattedScopes["{$scope->resource_name}:{$scope->action_name}"] = $scope->description ?: "Access {$scope->resource_name} {$scope->action_name}";
            }

            if (!empty($formattedScopes)) {
                Passport::tokensCan($formattedScopes);
            }
        } else {
            Passport::tokensCan([
                'profile:read' => 'Baca informasi profil dasar (nama, email, avatar)',
                'identity:read' => 'Baca identitas (NIP/ID Mitra, tipe)',
                'organization:read' => 'Baca info organisasi (satker, unit kerja, jabatan)',
                'phone:read' => 'Baca nomor telepon',
                'email:read' => 'Baca alamat email',
                'user:manage' => 'Akses penuh manajemen user',
            ]);
        }

        Passport::setDefaultScope(['profile:read']);

        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
