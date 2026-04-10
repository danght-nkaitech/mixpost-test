<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Util;

Route::prefix(Util::corePath().'/api')
    ->middleware(Util::config('middlewares')['api'])
    ->name('mixpost_e.api.')
    ->group(function () {
        require __DIR__.'/includes/panel.php';
    });
