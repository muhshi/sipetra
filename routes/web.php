<?php

use App\Http\Controllers\Auth\OAuthLogoutController;
use App\Livewire\Auth\Login;
use App\Livewire\PortalLandingPage;
use Illuminate\Support\Facades\Route;

Route::get('/', PortalLandingPage::class)->name('home');

// Custom SSO Login Route
Route::get('/login', Login::class)->name('login')->middleware('guest');

// Normal User Dashboard (where they go if they login directly instead of via OAuth)
Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware('auth')->name('dashboard');

// Federated Single Logout (SLO) & Normal Logout Routes
Route::get('/oauth/logout', [OAuthLogoutController::class, 'logout'])->name('oauth.logout');
Route::match(['get', 'post'], '/logout', [OAuthLogoutController::class, 'logout'])->name('logout');
