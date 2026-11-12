<?php

use App\Http\Controllers\Auth\OAuthLogoutController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\PortalLandingPage;
use Illuminate\Support\Facades\Route;

Route::get('/', PortalLandingPage::class)->name('home');

// Custom SSO Login & Register Routes
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/register', Register::class)->name('register')->middleware('guest');

// Normal User Dashboard (where they go if they login directly instead of via OAuth)
Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware('auth')->name('dashboard');

// Self-Service User Profile & Password Change Route
Route::get('/account', function () {
    return redirect('/?open_profile=1');
})->middleware('auth')->name('account.profile');

Route::get('/profile', function () {
    return redirect('/?open_profile=1');
})->middleware('auth')->name('user.profile');

// Federated Single Logout (SLO) & Normal Logout Routes
Route::get('/oauth/logout', [OAuthLogoutController::class, 'logout'])->name('oauth.logout');
Route::match(['get', 'post'], '/logout', [OAuthLogoutController::class, 'logout'])->name('logout');
