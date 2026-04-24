<?php

use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [UserApiController::class, 'show']);
});
