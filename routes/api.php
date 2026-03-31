<?php

use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [UserApiController::class, 'show'])
        ->middleware(CheckToken::using('profile:read'));

    Route::get('/user/identity', [UserApiController::class, 'identity'])
        ->middleware(CheckToken::using('identity:read'));

    Route::get('/user/organization', [UserApiController::class, 'organization'])
        ->middleware(CheckToken::using('organization:read'));
});
