<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Base\Middleware\RedirectIfAuthenticated;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Onboarding\RegisterController;
use Inovector\MixpostEnterprise\Http\Base\Middleware\AllowRegister;

Route::middleware([
    AllowRegister::class,
    RedirectIfAuthenticated::class,
])->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
});
