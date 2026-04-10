<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Base\Middleware\Admin;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\WorkspaceLockedController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\AddGenericSubscriptionController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\CancelSubscriptionController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\ChangeSubscriptionPlanController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs\BillingConfigController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs\OnboardingConfigController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs\ScriptsConfigController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs\SystemConfigController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs\ThemeConfigController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\DashboardController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\DeleteReceiptsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\DeleteUsersController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\DeleteWorkspacesController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\DownloadReceiptController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\MetricsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\PaymentPlatformsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\PlansController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\PortalPaymentPlatformController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\ReceiptsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\RemoveGenericSubscriptionController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\SubscriptionInfoController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\UserItemsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\UsersController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\WorkspaceItemsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\WorkspacesController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\WorkspaceUsersController;

Route::middleware([Admin::class])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::prefix('metrics')->name('metrics.')->group(function () {
        Route::get('users', [MetricsController::class, 'users'])->name('users');
        Route::get('workspaces', [MetricsController::class, 'workspaces'])->name('workspaces');
        Route::get('subscriptions', [MetricsController::class, 'subscriptions'])->name('subscriptions');
    });

    Route::prefix('workspaces')->name('workspaces.')->group(function () {
        Route::get('/', [WorkspacesController::class, 'index'])->name('index');
        Route::get('create', [WorkspacesController::class, 'create'])->name('create');
        Route::post('/', [WorkspacesController::class, 'store'])->name('store');
        Route::get('{workspace}', [WorkspacesController::class, 'view'])->name('view');
        Route::get('{workspace}/edit', [WorkspacesController::class, 'edit'])->name('edit');
        Route::put('{workspace}', [WorkspacesController::class, 'update'])->name('update');
        Route::delete('{workspace}', [WorkspacesController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteWorkspacesController::class)->name('multipleDelete');

        Route::prefix('resources')->name('resources.')->group(function () {
            Route::get('items', WorkspaceItemsController::class)->name('items');
        });

        Route::prefix('{workspace}/users')->name('users.')->group(function () {
            Route::post('/', [WorkspaceUsersController::class, 'store'])->name('store');
            Route::put('/', [WorkspaceUsersController::class, 'update'])->name('update');
            Route::delete('/', [WorkspaceUsersController::class, 'destroy'])->name('delete');
        });

        Route::get('{workspace}/locked', WorkspaceLockedController::class)->name('locked');

        Route::put('{workspace}/portal-payment-platform', PortalPaymentPlatformController::class)->name('portalPaymentPlatform');

        Route::prefix('{workspace}/subscription')->name('subscription.')->group(function () {
            Route::get('info', SubscriptionInfoController::class)->name('info');
            Route::post('change-plan', ChangeSubscriptionPlanController::class)->name('changePlan');
            Route::post('cancel', CancelSubscriptionController::class)->name('cancel');

            Route::post('generic', AddGenericSubscriptionController::class)->name('addGeneric');
            Route::delete('generic', RemoveGenericSubscriptionController::class)->name('removeGeneric');
        });
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('create', [UsersController::class, 'create'])->name('create');
        Route::post('/', [UsersController::class, 'store'])->name('store');
        Route::get('{user}', [UsersController::class, 'view'])->name('view');
        Route::get('{user}/edit', [UsersController::class, 'edit'])->name('edit');
        Route::put('{user}/update', [UsersController::class, 'update'])->name('update');
        Route::delete('{user}', [UsersController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteUsersController::class)->name('multipleDelete');

        Route::prefix('resources')->name('resources.')->group(function () {
            Route::get('items', UserItemsController::class)->name('items');
        });
    });

    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlansController::class, 'index'])->name('index');
        Route::get('create', [PlansController::class, 'create'])->name('create');
        Route::post('/', [PlansController::class, 'store'])->name('store');
        Route::get('{plan}', [PlansController::class, 'view'])->name('view');
        Route::get('{plan}/edit', [PlansController::class, 'edit'])->name('edit');
        Route::put('{plan}', [PlansController::class, 'update'])->name('update');
        Route::delete('{plan}', [PlansController::class, 'delete'])->name('delete');
    });

    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/', [ReceiptsController::class, 'index'])->name('index');
        Route::get('create', [ReceiptsController::class, 'create'])->name('create');
        Route::post('/', [ReceiptsController::class, 'store'])->name('store');
        Route::get('{receipt}', [ReceiptsController::class, 'view'])->name('view');
        Route::get('{receipt}/edit', [ReceiptsController::class, 'edit'])->name('edit');
        Route::get('{receipt}/download', DownloadReceiptController::class)->name('download');
        Route::put('{receipt}', [ReceiptsController::class, 'update'])->name('update');
        Route::delete('{receipt}', [ReceiptsController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteReceiptsController::class)->name('multipleDelete');
    });

    Route::prefix('configs')->name('configs.')->group(function () {
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SystemConfigController::class, 'view'])->name('view');
            Route::put('/', [SystemConfigController::class, 'update'])->name('update');
        });

        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/', [BillingConfigController::class, 'view'])->name('view');
            Route::put('/', [BillingConfigController::class, 'update'])->name('update');
        });

        Route::prefix('onboarding')->name('onboarding.')->group(function () {
            Route::get('/', [OnboardingConfigController::class, 'view'])->name('view');
            Route::put('/', [OnboardingConfigController::class, 'update'])->name('update');
        });

        Route::prefix('scripts')->name('scripts.')->group(function () {
            Route::get('/', [ScriptsConfigController::class, 'view'])->name('view');
            Route::put('/', [ScriptsConfigController::class, 'update'])->name('update');
        });

        Route::prefix('theme')->name('theme.')->group(function () {
            Route::get('/', [ThemeConfigController::class, 'view'])->name('view');
            Route::put('/', [ThemeConfigController::class, 'update'])->name('update');
        });
    });

    Route::prefix('payment-platforms')->name('payment-platforms.')->group(function () {
        Route::get('/', [PaymentPlatformsController::class, 'index'])->name('index');
        Route::put('/', [PaymentPlatformsController::class, 'update'])->name('update');
    });
});
