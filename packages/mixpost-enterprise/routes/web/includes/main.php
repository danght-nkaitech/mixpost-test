<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Base\Middleware\EnsurePasswordConfirmed;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\AcceptInvitationController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\CreateWorkspaceController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\DeclineInvitationController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\DeleteAccountController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\EmailVerificationNoticeController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\InvitationController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\ResendEmailVerificationController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main\VerifyEmailController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\ImpersonateController;
use Inovector\MixpostEnterprise\Http\Base\Middleware\AllowMultipleWorkspaces;
use Inovector\MixpostEnterprise\Http\Base\Middleware\EnsureEmailIsVerified;

Route::middleware([AllowMultipleWorkspaces::class])
    ->prefix('workspace')
    ->name('workspace.')
    ->group(function () {
        Route::get('create', [CreateWorkspaceController::class, 'create'])->name('create');
        Route::post('store', [CreateWorkspaceController::class, 'store'])->name('store');
    });

Route::prefix('invitations')->name('invitations.')->group(function () {
    Route::get('{invitation}', InvitationController::class)->name('view');
    Route::post('{invitation}', AcceptInvitationController::class)->name('accept');
    Route::delete('{invitation}', DeclineInvitationController::class)->name('decline');
});

Route::prefix('impersonate')->name('impersonate.')->group(function () {
    Route::post('{user}', [ImpersonateController::class, 'startImpersonating'])->name('start');
    Route::delete('/', [ImpersonateController::class, 'stopImpersonating'])->name('stop');
});

Route::prefix('email/verification')->name('emailVerification.')->group(function () {
    Route::get('notice', EmailVerificationNoticeController::class)
        ->withoutMiddleware(EnsureEmailIsVerified::class)
        ->name('notice');

    Route::post('resend', ResendEmailVerificationController::class)
        ->withoutMiddleware(EnsureEmailIsVerified::class)
        ->middleware('throttle:6,1')
        ->name('resend');

    Route::get('verify/{id}/{hash}', VerifyEmailController::class)
        ->withoutMiddleware(EnsureEmailIsVerified::class)
        ->middleware('signed')
        ->name('verify');
});

Route::delete('delete-account', DeleteAccountController::class)
    ->withoutMiddleware(EnsureEmailIsVerified::class)
    ->middleware(EnsurePasswordConfirmed::class)
    ->name('deleteAccount');
