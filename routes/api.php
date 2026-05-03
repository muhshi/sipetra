<?php

use App\Http\Controllers\Api\MasterUserController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SSO User Profile API
|--------------------------------------------------------------------------
| Digunakan oleh client SSO untuk mendapatkan profil user yang sedang login.
| Autentikasi: OAuth2 Bearer Token (atas nama user).
*/
Route::middleware('auth:api')->group(function () {
    Route::get('/user', [UserApiController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Master Data API (Machine-to-Machine)
|--------------------------------------------------------------------------
| Digunakan oleh aplikasi client untuk sinkronisasi master data pegawai & mitra.
| Autentikasi: Personal Access Token (server-to-server, bukan atas nama user).
| Rate limit: 60 request per menit per token.
|
| Dokumentasi: docs/openapi.yaml & docs/API_MASTER_USERS.md
*/
Route::middleware(['auth:api', 'master.token', 'throttle:60,1'])
    ->prefix('master')
    ->name('api.master.')
    ->group(function () {
        Route::get('/users', [MasterUserController::class, 'index'])->name('users.index');
        Route::get('/users/{sipetra_id}', [MasterUserController::class, 'show'])->name('users.show');
    });
