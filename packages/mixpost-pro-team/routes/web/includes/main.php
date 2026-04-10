<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Features;
use Inovector\Mixpost\Http\Base\Controllers\Main\AccessTokensController;
use Inovector\Mixpost\Http\Base\Controllers\Main\ConfirmPasswordController;
use Inovector\Mixpost\Http\Base\Controllers\Main\DeleteAccessTokensController;
use Inovector\Mixpost\Http\Base\Controllers\Main\ExtractUrlMetaController;
use Inovector\Mixpost\Http\Base\Controllers\Main\HomeController;
use Inovector\Mixpost\Http\Base\Controllers\Main\ProfileController;
use Inovector\Mixpost\Http\Base\Controllers\Main\TwoFactorAuthController;
use Inovector\Mixpost\Http\Base\Controllers\Main\UpdateAuthUserController;
use Inovector\Mixpost\Http\Base\Controllers\Main\UpdateAuthUserPasswordController;
use Inovector\Mixpost\Http\Base\Controllers\Main\UpdateAuthUserPreferencesController;
use Inovector\Mixpost\Http\Base\Middleware\EnsurePasswordConfirmed;

Route::get('/', HomeController::class)->name('home');

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::put('preferences', UpdateAuthUserPreferencesController::class)->name('updatePreferences');
    Route::put('user', UpdateAuthUserController::class)->name('updateUser');
    Route::put('password', UpdateAuthUserPasswordController::class)->name('updatePassword');
    Route::post('confirm-password', ConfirmPasswordController::class)->name('confirmPassword');

    Route::post('ensure-password-confirmed', function () {
        return response()->json(true);
    })->middleware(EnsurePasswordConfirmed::class)->name('ensurePasswordConfirmed');

    if (Features::isTwoFactorAuthEnabled()) {
        Route::prefix('two-factor-auth')
            ->name('two-factor-auth.')
            ->middleware(EnsurePasswordConfirmed::class)
            ->group(function () {
                Route::post('enable', [TwoFactorAuthController::class, 'enable'])->name('enable');
                Route::post('confirm', [TwoFactorAuthController::class, 'confirm'])->name('confirm');
                Route::get('recovery-codes', [TwoFactorAuthController::class, 'showRecoveryCodes'])->name('showRecoveryCodes');
                Route::post('regenerate-recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])->name('regenerateRecoveryCodes');
                Route::delete('disable', [TwoFactorAuthController::class, 'disable'])->name('disable');
            });
    }

    if (Features::isApiAccessTokenEnabled()) {
        Route::prefix('access-tokens')
            ->name('accessTokens.')
            ->group(function () {
                Route::get('/', [AccessTokensController::class, 'index'])->name('index');
                Route::post('/', [AccessTokensController::class, 'store'])->middleware(EnsurePasswordConfirmed::class)->name('store');
                Route::delete('{token}', [AccessTokensController::class, 'delete'])->middleware(EnsurePasswordConfirmed::class)->name('delete');
                Route::delete('/', DeleteAccessTokensController::class)->middleware(EnsurePasswordConfirmed::class)->name('deleteMultiple');
            });
    }
});

Route::get('extract-url-meta', ExtractUrlMetaController::class)->name('extractUrlMeta');

Route::get('refresh-csrf-token', function (Request $request) {
    $request->session()->regenerateToken();

    return response(Config::get('app.name'));
})->name('refreshCsrfToken');
