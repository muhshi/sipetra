<?php

use App\Http\Controllers\Auth\OAuthAuthorizationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Override Passport default agar kita bisa intercept evaluasi akses
Route::get('/oauth/authorize', [OAuthAuthorizationController::class, 'authorize'])
    ->middleware(['web'])
    ->name('passport.authorizations.authorize');
