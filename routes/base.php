<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Base;
use Pterodactyl\Http\Middleware\RequireTwoFactorAuthentication;

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();
Route::get('/account', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('account');

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->where('namespace', '.*');

Route::get('/coins/linkvertise/claim/{token}', Base\LinkvertiseClaimController::class)
    ->name('coins.linkvertise.claim');

// Support view: back from the user's account to the staff member's own.
Route::post('/impersonate/leave', [Base\ImpersonationController::class, 'leave'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('impersonate.leave');

Route::get('/{react}', [Base\IndexController::class, 'index'])
    ->where('react', '^(?!(\/)?(api|auth|admin|daemon)).+');
