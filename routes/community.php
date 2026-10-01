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

// Second step of "forgot password" (link from the e-mail).
Route::get('/auth/password/confirm/{user}/{hash}', [\Pterodactyl\Http\Controllers\Auth\ForgotPasswordController::class, 'confirm'])
    ->middleware([\Illuminate\Routing\Middleware\ValidateSignature::class, 'throttle:10,1,password-confirm'])
    ->whereNumber('user')
    ->name('auth.password.confirm');

Route::get('/branding/{file}', [Base\BrandingController::class, 'file'])->name('branding.file');
Route::get('/manifest.webmanifest', [Base\BrandingController::class, 'manifest'])->name('manifest');

Route::get('/auth/verify-email/{id}/{hash}', Base\EmailVerificationController::class)
    ->middleware([\Illuminate\Routing\Middleware\ValidateSignature::class, 'throttle:10,1,verify-email'])
    ->whereNumber('id')
    ->name('auth.verify-email');

Route::middleware('throttle:20,1,discord')->group(function () {
    Route::get('/auth/discord', [Base\DiscordAuthController::class, 'redirect'])->name('auth.discord');
    Route::get('/auth/discord/callback', [Base\DiscordAuthController::class, 'callback'])->name('auth.discord.callback');
});
