<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Livewire\PortalLandingPage;

Route::get('/', PortalLandingPage::class)->name('home');

// Custom SSO Login Route
Route::get('/login', Login::class)->name('login')->middleware('guest');

// Normal User Dashboard (where they go if they login directly instead of via OAuth)
Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware('auth')->name('dashboard');

// Logout Route
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');
