<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowRequestController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SavePostController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryViewerController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function (): void {

    Route::controller(AuthController::class)->group(function (): void {
        Route::post('/check-verification-code', 'checkVerificationCode');
        Route::post('/register/verification/', 'verify');
        Route::post('/reset-password', 'resetPassword')->middleware('throttle:5,1');
        Route::post('/logout', 'logout');
    });

    Route::controller(ProfileController::class)->group(function (): void {
        Route::get('/accounts/{username}', 'index');
        Route::put('/accounts/edit', 'update');
        Route::post('/accounts/edit', 'update');
        Route::delete('/accounts/edit', 'destroy');
    });

    Route::controller(UserProfileController::class)->group(function (): void {
        Route::post('/{username}/follow', 'store');
        Route::delete('/{username}/unfollow', 'destroy');
    });
    Route::controller(FollowRequestController::class)->group(function (): void {
        Route::get('/follow-requests', 'pendingFollowRequests');
        Route::post('/follow-requests/{username}/accept', 'store');
        Route::post('/follow-requests/{username}/decline', 'destroy');
    });

    Route::controller(PostController::class)->group(function (): void {
        Route::get('/posts/{user:username}', 'index');
        Route::post('/posts', 'store');
        Route::put('/posts/{user:username}/{post}', 'update');
        Route::delete('/posts/{user:username}/{post}', 'destroy');
    });
    Route::controller(SavePostController::class)->group(function (): void {
        Route::get('/saved-posts', 'index');
        Route::post('/saved-posts/{post}', 'store');
        Route::delete('/saved-posts/{post}', 'destroy');
    });
    Route::controller(MediaController::class)->group(function (): void {
        Route::post('/{user:username}/posts/{post}/media', 'store');
        Route::delete('/{user:username}/posts/{post}/media/{media}', 'destroy');
    });
    Route::controller(CommentController::class)->group(function (): void {
        Route::post('/posts/{user:username}/{post}/comment', 'store');
        Route::put('/posts/{user:username}/{post}/comment/{comment}', 'update');
        Route::delete('/posts/{user:username}/{post}/comment/{comment}', 'destroy');
    });
    Route::controller(ReplyController::class)->group(function (): void {
        Route::post('/posts/{user:username}/{post}/{comment}/replies', 'store');
        Route::put('/posts/{user:username}/{post}/{comment}/replies/{reply}', 'update');
        Route::delete('/posts/{user:username}/{post}/{comment}/replies/{reply}', 'destroy');
    });
    Route::controller(LikeController::class)->group(function (): void {
        Route::post('/like/{model}/{id}', 'store');
    });
    Route::controller(StoryController::class)->group(function (): void {
        Route::get('/stories/{user:username}', 'index');
        Route::post('/stories/{user:username}', 'store');
        Route::put('/stories/{user:username}/{story}', 'update');
        Route::delete('/stories/{user:username}/{story}', 'destroy');
    });
    Route::controller(StoryViewerController::class)->group(function (): void {
        Route::get('/stories/{story}/viewers', 'index');
        Route::post('/stories/{story}/view', 'store');
    });
    Route::controller(NoteController::class)->group(function (): void {
        Route::get('/notes/{user:username}', 'index');
        Route::post('/notes/{user:username}', 'store');
        Route::put('/notes/{user:username}/{note}', 'update');
        Route::delete('/notes/{user:username}/{note}', 'destroy');
    });
});
