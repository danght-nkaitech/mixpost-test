<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Features;
use Inovector\Mixpost\Http\Base\Controllers\Auth\AuthenticatedController;
use Inovector\Mixpost\Http\Base\Controllers\Auth\FirebaseLoginController;
use Inovector\Mixpost\Http\Base\Controllers\Auth\InstallationController;
use Inovector\Mixpost\Http\Base\Controllers\Auth\NewPasswordController;
use Inovector\Mixpost\Http\Base\Controllers\Auth\PasswordResetLinkController;
use Inovector\Mixpost\Http\Base\Controllers\Auth\TwoFactorAuthSessionController;
use Inovector\Mixpost\Http\Base\Middleware\RedirectIfAuthenticated;
use Inovector\Mixpost\Mixpost;

Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::get('login', [AuthenticatedController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedController::class, 'store']);
    Route::post('auth/firebase', FirebaseLoginController::class)->name('firebase.login');

    Route::get('installation', [InstallationController::class, 'create'])->name('installation');
    Route::post('installation', [InstallationController::class, 'store']);

    if (Features::isTwoFactorAuthEnabled()) {
        Route::get('two-factor-login', [TwoFactorAuthSessionController::class, 'create'])->name('two-factor.login');
        Route::post('two-factor-login', [TwoFactorAuthSessionController::class, 'store']);
    }

    if (Features::isForgotPasswordEnabled()) {
        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    }
});

Route::middleware(Mixpost::getWebDashboardMiddlewares())
    ->post('logout', [AuthenticatedController::class, 'destroy'])
    ->name('logout');
