<?php

use Illuminate\Support\Facades\Route;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Guest\InvitationController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Guest\PaymentWebhookController;

Route::get('invitation/{invitation}', InvitationController::class)
    ->name('invitation');

Route::any('payment-webhook', PaymentWebhookController::class)
    ->name('paymentWebhook');
