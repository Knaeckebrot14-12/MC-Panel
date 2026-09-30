<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Base;

/*
|--------------------------------------------------------------------------
| Public routes (no login required)
|--------------------------------------------------------------------------
|
| Status page, e-mail confirmation links and the Discord login.
|
*/
Route::get('/status', Base\StatusPageController::class)->name('status');

Route::get('/auth/verify-email/{id}/{hash}', Base\EmailVerificationController::class)
    ->middleware([\Illuminate\Routing\Middleware\ValidateSignature::class, 'throttle:10,1'])
    ->whereNumber('id')
    ->name('auth.verify-email');

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/auth/discord', [Base\DiscordAuthController::class, 'redirect'])->name('auth.discord');
    Route::get('/auth/discord/callback', [Base\DiscordAuthController::class, 'callback'])->name('auth.discord.callback');
});
