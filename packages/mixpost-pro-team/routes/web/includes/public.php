<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Base\Controllers\Public\BlueskyOauthMetaController;
use Inovector\Mixpost\Http\Base\Controllers\Public\ManifestController;
use Inovector\Mixpost\Http\Base\Controllers\Public\PagesController;
use Inovector\Mixpost\Http\Base\Middleware\BlueskyService;
use Inovector\Mixpost\Util;

Route::name('mixpost.')->group(function () {
    Route::get('manifest.json', ManifestController::class)->name('manifest');

    Route::prefix('bluesky/oauth')->name('blueskyOauth.')->middleware(BlueskyService::class)->group(function () {
        Route::get('client-metadata.json', [BlueskyOauthMetaController::class, 'clientMeta'])->name('clientMeta');
        Route::get('jwks', [BlueskyOauthMetaController::class, 'jwks'])->name('jwks');
    });

    Route::prefix(Util::config('public_pages_prefix', ''))
        ->name('pages.')
        ->group(function () {
            Route::get('{slug?}', PagesController::class)->name('show');
        });
});
