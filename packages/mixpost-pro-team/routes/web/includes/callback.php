<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Base\Controllers\Main\CallbackSocialProviderController;
use Inovector\Mixpost\Http\Base\Controllers\Public\UninstallCallbackSocialProviderController;
use Inovector\Mixpost\Http\Base\Middleware\CheckWorkspaceUser;
use Inovector\Mixpost\Http\Base\Middleware\HandleInertiaRequests;
use Inovector\Mixpost\Http\Base\Middleware\IdentifyWorkspaceForCallback;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Util;

$prefix = Util::config('force_core_path_callback_to_native', false) ?
    'mixpost' :
    Util::corePath();

$middleware = array_merge(
    Mixpost::getWebAppMiddlewares(),
    Mixpost::getWebDashboardMiddlewares(),
    [
        HandleInertiaRequests::class,
        IdentifyWorkspaceForCallback::class,
        CheckWorkspaceUser::class,
    ]
);

Route::middleware($middleware)
    ->prefix($prefix)
    ->get('callback/{provider}', CallbackSocialProviderController::class)
    ->name('mixpost.callbackSocialProvider');

Route::prefix($prefix)
    ->post('uninstall-callback/{provider}', UninstallCallbackSocialProviderController::class)
    ->name('mixpost.uninstallCallbackSocialProvider');
