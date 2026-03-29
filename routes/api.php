<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:4,1'])
    ->controller(AuthController::class)
    ->group(function (): void {

        Route::post('/register', 'register');

        Route::post('/login', 'login');

        Route::post('/forgot-password', 'forgetPassword');
    });

require __DIR__.'/auth.php';

Route::get('/{username}', [UserProfileController::class, 'index']);
Route::get('/{username}/posts', [PostController::class, 'index']);
