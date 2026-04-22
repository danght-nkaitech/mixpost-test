<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Features;
use Inovector\Mixpost\Http\Base\Controllers\Main\AccessTokensController;
use Inovector\Mixpost\Util;

Route::prefix(Util::corePath().'/api')
    ->middleware(['api', \Inovector\Mixpost\Http\Api\Middleware\ForceJsonResponse::class])
    ->group(function () {
        Route::post('issue-token', [AccessTokensController::class, 'issueForExternalSystem'])->name('mixpost.api.issue-token');
    });

if (Features::isApiAccessTokenEnabled()) {
    Route::prefix(Util::corePath().'/api')
        ->middleware(Util::config('middlewares')['api'])
        ->name('mixpost.api.')
        ->group(function () {
            Route::get('ping', function () {
                return response()->json(['status' => 'ok']);
            })->name('ping');

            require __DIR__.'/includes/workspace.php';
        });
}
