<?php

namespace App\Providers;

use App\Http\Controllers\Auth\OAuthAuthorizationController;
use App\Models\PassportClient;
use App\Policies\ClientPolicy;
use App\Settings\SystemSettings;
use DaniHidayatX\ImageOptimizer\ImageProcessor;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Laravel\Passport\Passport;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\CreateClient;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\EditClient;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\ViewClient;

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
            return view('vendor.passport.authorize', $parameters);
        });

        Passport::tokensCan([
            'profile:read' => 'Baca informasi profil dasar (nama, email, avatar, tipe user)',
            'identity_pegawai:read' => 'Identitas khusus pegawai (NIP Baru/Lama)',
            'identity_mitra:read' => 'Baca identitas khusus mitra (SOBAT ID, NIK)',
            'employee:read' => 'Baca data detil kepegawaian (Jabatan, Golongan, Masa Kerja, Agama, dsb)',
            'contact:read' => 'Baca data kontak (Nomor HP, Alamat)',
            'roles:read' => 'Baca hak akses (Roles & Permissions)',
        ]);

        Passport::useClientModel(PassportClient::class);

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

        // Fallback registration for image-optimizer macros
        if (class_exists(FileUpload::class) && ! FileUpload::hasMacro('optimize')) {
            $registerMacro = function ($name, $callback) {
                if (! FileUpload::hasMacro($name)) {
                    FileUpload::macro($name, $callback);
                }
            };

            $registerMacro('optimize', function ($format = 'webp', $quality = null) {
                $this->imageOptimization = $this->imageOptimization ?? [];
                $this->imageOptimization['format'] = $format;
                $this->imageOptimization['quality'] = $quality;
                if (FileUpload::hasMacro('ensureOptimizerHook')) {
                    $this->ensureOptimizerHook();
                }

                return $this;
            });

            $registerMacro('maxImageWidth', function ($width) {
                $this->imageOptimization = $this->imageOptimization ?? [];
                $this->imageOptimization['max_width'] = $width;
                if (FileUpload::hasMacro('ensureOptimizerHook')) {
                    $this->ensureOptimizerHook();
                }

                return $this;
            });

            // We need ensureOptimizerHook too
            $registerMacro('ensureOptimizerHook', function () {
                if ($this->hasOptimizerHook ?? false) {
                    return;
                }
                $this->hasOptimizerHook = true;
                $this->saveUploadedFileUsing(function ($component, $file, $record = null) {
                    return $component->processAndStoreImage($file);
                });
            });

            $registerMacro('processAndStoreImage', function ($file) {
                $settings = $this->imageOptimization ?? [];
                $compressedImage = ImageProcessor::process($file, [
                    'format' => $this->evaluate($settings['format'] ?? null),
                    'max_width' => $this->evaluate($settings['max_width'] ?? null),
                    'quality' => $this->evaluate($settings['quality'] ?? null),
                ]);
                $filename = $this->getUploadedFileNameForStorage($file);
                if ($format = $this->evaluate($settings['format'] ?? null)) {
                    $filename = pathinfo($filename, PATHINFO_FILENAME).'.'.$format;
                }
                $path = ltrim($this->getDirectory().'/'.$filename, '/');
                Storage::disk($this->getDiskName())->put($path, $compressedImage);

                return $path;
            });
        }
    }
}
