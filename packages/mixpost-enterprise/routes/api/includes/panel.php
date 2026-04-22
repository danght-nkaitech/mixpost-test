<?php

use Illuminate\Support\Facades\Route;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Receipts\DeleteReceiptsController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Receipts\ReceiptsController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Users\DeleteUsersController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Users\UsersController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\DeleteWorkspacesController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription\AddGenericSubscriptionController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription\CancelSubscriptionController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription\ChangeSubscriptionPlanController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription\NewSubscriptionController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription\RemoveGenericSubscriptionController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription\ResumeSubscriptionController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription\SubscriptionController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Users\WorkspaceUsersController;
use Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\WorkspacesController;
use Inovector\MixpostEnterprise\Http\Api\Middleware\Admin;

Route::prefix('panel')->name('panel.')->middleware([Admin::class])->group(function () {
    Route::prefix('workspaces')->name('workspaces.')->group(function () {
        Route::get('/', [WorkspacesController::class, 'index'])->name('index');
        Route::get('find-by-email', [WorkspacesController::class, 'findByEmail'])->name('findByEmail');
        Route::post('/', [WorkspacesController::class, 'store'])->name('store');
        Route::get('{workspace}', [WorkspacesController::class, 'show'])->name('show');
        Route::put('{workspace}', [WorkspacesController::class, 'update'])->name('update');
        Route::delete('{workspace}', [WorkspacesController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteWorkspacesController::class)->name('multipleDelete');

        Route::prefix('{workspace}/users')->name('users.')->group(function () {
            Route::post('/', [WorkspaceUsersController::class, 'store'])->name('store');
            Route::put('/', [WorkspaceUsersController::class, 'update'])->name('update');
            Route::delete('/', [WorkspaceUsersController::class, 'destroy'])->name('delete');
        });

        Route::prefix('{workspace}/subscription')->name('subscription.')->group(function () {
            Route::get('/', [SubscriptionController::class, 'show'])->name('show');
            Route::post('/', [SubscriptionController::class, 'store'])->name('store');
            Route::put('/', [SubscriptionController::class, 'update'])->name('update');
            Route::delete('/', [SubscriptionController::class, 'delete'])->name('delete');

            Route::post('new', NewSubscriptionController::class)->name('new');
            Route::put('change-plan', ChangeSubscriptionPlanController::class)->name('changePlan');
            Route::post('cancel', CancelSubscriptionController::class)->name('cancel');
            Route::post('resume', ResumeSubscriptionController::class)->name('resume');

            Route::post('generic', AddGenericSubscriptionController::class)->name('addGeneric');
            Route::delete('generic', RemoveGenericSubscriptionController::class)->name('removeGeneric');
        });
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::post('/', [UsersController::class, 'store'])->name('store');
        Route::get('{user}', [UsersController::class, 'show'])->name('show');
        Route::put('{user}', [UsersController::class, 'update'])->name('update');
        Route::delete('{user}', [UsersController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteUsersController::class)->name('multipleDelete');
    });

    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/', [ReceiptsController::class, 'index'])->name('index');
        Route::post('/', [ReceiptsController::class, 'store'])->name('store');
        Route::get('{receipt}', [ReceiptsController::class, 'show'])->name('show');
        Route::put('{receipt}', [ReceiptsController::class, 'update'])->name('update');
        Route::delete('{receipt}', [ReceiptsController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteReceiptsController::class)->name('multipleDelete');
    });
});
