<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Base\Middleware\Localization;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Util;
use Inovector\MixpostEnterprise\Http\Base\Middleware\HandleInertiaRequests;
use Inovector\MixpostEnterprise\Util as EnterpriseUtil;

Route::prefix(EnterpriseUtil::corePath(true))
    ->name('mixpost_e.')
    ->middleware(Mixpost::getWebAppMiddlewares())
    ->group(function () {
        // Enterprise console routes
        Route::middleware(array_merge(
            Mixpost::getWebDashboardMiddlewares(),
            [HandleInertiaRequests::class]
        ))->group(function () {
            require __DIR__.'/includes/panel.php';
        });
    });

Route::prefix(Util::corePath())
    ->name('mixpost_e.')
    ->middleware(['web', Localization::class])
    ->group(function () {
        // Onboarding routes
        Route::middleware([
            HandleInertiaRequests::class,
        ])->group(function () {
            require __DIR__.'/includes/onboarding.php';
        });

        // Dashboard routes
        Route::middleware(array_merge(
            Mixpost::getWebDashboardMiddlewares(),
            [HandleInertiaRequests::class]
        ))->group(function () {
            require __DIR__.'/includes/main.php';

            require __DIR__.'/includes/workspace.php';
        });

        // Guest Routes
        require __DIR__.'/includes/guest.php';
    });
