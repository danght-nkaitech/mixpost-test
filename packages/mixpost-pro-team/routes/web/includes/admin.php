<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Base\Controllers\Admin\BlocksController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Configs\AIConfigController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Configs\GeneralConfigController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Configs\GifConfigController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Configs\MediaConfigController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Configs\ThemeConfigController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\DashboardController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\DeletePagesController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\DeleteUsersController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\DeleteWorkspacesController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\GeneratePageSamplesController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\PagesController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Services\Bluesky\GenerateBlueskyPrivateKeyController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Services\Mastodon\DeleteMastodonServiceController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Services\Mastodon\RecreateMastodonServiceController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\ServicesController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\SystemLogsController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\SystemStatusController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\UploadFileController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\UserItemsController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\UsersController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Webhook\DeleteWebhooksController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Webhook\ResendWebhookController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Webhook\UpdateWebhookSecret;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Webhook\WebhookDeliveriesController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\Webhook\WebhooksController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\WorkspaceItemsController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\WorkspacesController;
use Inovector\Mixpost\Http\Base\Controllers\Admin\WorkspaceUsersController;
use Inovector\Mixpost\Http\Base\Controllers\Workspace\CreateMastodonAppController;
use Inovector\Mixpost\Http\Base\Middleware\Admin;
use Inovector\Mixpost\Http\Base\Middleware\EnsurePasswordConfirmed;
use Inovector\Mixpost\Http\Base\Middleware\EnterpriseConsoleRedirects;
use Inovector\Mixpost\Http\Base\Middleware\HandleInertiaRequests;
use Inovector\Mixpost\Http\Base\Middleware\SystemWebhook;

Route::prefix('admin')->middleware([Admin::class])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboardAdmin');

    Route::prefix('workspaces')->name('workspaces.')->middleware(EnterpriseConsoleRedirects::class)->group(function () {
        Route::get('/', [WorkspacesController::class, 'index'])->name('index');
        Route::get('/', [WorkspacesController::class, 'index'])->name('index');
        Route::get('create', [WorkspacesController::class, 'create'])->name('create');
        Route::post('/', [WorkspacesController::class, 'store'])->name('store');
        Route::get('{workspace}', [WorkspacesController::class, 'view'])->name('view');
        Route::get('{workspace}/edit', [WorkspacesController::class, 'edit'])->name('edit');
        Route::put('{workspace}', [WorkspacesController::class, 'update'])->name('update');
        Route::delete('{workspace}', [WorkspacesController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteWorkspacesController::class)->name('deleteMultiple');

        Route::prefix('resources')->name('resources.')->group(function () {
            Route::get('items', WorkspaceItemsController::class)->name('items');
        });

        Route::prefix('{workspace}/users')->name('users.')->group(function () {
            Route::post('/', [WorkspaceUsersController::class, 'store'])->name('store');
            Route::put('/', [WorkspaceUsersController::class, 'update'])->name('update');
            Route::delete('/', [WorkspaceUsersController::class, 'destroy'])->name('delete');
        });
    });

    Route::prefix('users')->name('users.')->middleware(EnterpriseConsoleRedirects::class)->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('create', [UsersController::class, 'create'])->name('create');
        Route::post('/', [UsersController::class, 'store'])->name('store');
        Route::get('{user}', [UsersController::class, 'view'])->name('view');
        Route::get('{user}/edit', [UsersController::class, 'edit'])->name('edit');
        Route::put('{user}/update', [UsersController::class, 'update'])->name('update');
        Route::delete('{user}', [UsersController::class, 'delete'])->name('delete');
        Route::delete('/', DeleteUsersController::class)->name('deleteMultiple');

        Route::prefix('resources')->name('resources.')->group(function () {
            Route::get('items', UserItemsController::class)->name('items');
        });
    });

    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServicesController::class, 'index'])->name('index');
        Route::put('{service}', [ServicesController::class, 'update'])->name('update');

        Route::post('generate-bluesky-private-key', GenerateBlueskyPrivateKeyController::class)
            ->name('generateBlueskyPrivateKey');

        // TODO: move this to the workspace routes
        Route::post('create-mastodon-app', CreateMastodonAppController::class)
            ->withoutMiddleware([HandleInertiaRequests::class, Admin::class])
            ->name('createMastodonApp');

        Route::delete('delete-mastodon-service', DeleteMastodonServiceController::class)
            ->name('deleteMastodonService');

        Route::post('recreate-mastodon-service', RecreateMastodonServiceController::class)
            ->name('recreateMastodonService');
    });

    Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [PagesController::class, 'index'])->name('index');
        Route::get('create', [PagesController::class, 'create'])->name('create');
        Route::post('/', [PagesController::class, 'store'])->name('store');
        Route::get('{page}', [PagesController::class, 'edit'])->name('edit');
        Route::put('{page}', [PagesController::class, 'update'])->name('update');
        Route::delete('{page}', [PagesController::class, 'delete'])->name('delete');
        Route::delete('/', DeletePagesController::class)->name('deleteMultiple');

        Route::post('generate-samples', GeneratePageSamplesController::class)->name('generateSamples');
    });

    Route::prefix('blocks')->name('blocks.')->group(function () {
        Route::post('/', [BlocksController::class, 'store'])->name('store');
        Route::put('{block}', [BlocksController::class, 'update'])->name('update');
        Route::delete('{block}', [BlocksController::class, 'delete'])->name('delete');
    });

    Route::prefix('configs')->name('configs.')->group(function () {
        Route::prefix('general')->name('general.')->group(function () {
            Route::get('/', [GeneralConfigController::class, 'form'])->name('form');
            Route::put('/', [GeneralConfigController::class, 'update'])->name('update');
        });

        Route::prefix('theme')->name('theme.')->group(function () {
            Route::get('/', [ThemeConfigController::class, 'form'])->name('form');
            Route::put('/', [ThemeConfigController::class, 'update'])->name('update');
        });

        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/', [AIConfigController::class, 'form'])->name('form');
            Route::put('/', [AIConfigController::class, 'update'])->name('update');
        });

        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [MediaConfigController::class, 'form'])->name('form');
            Route::put('/', [MediaConfigController::class, 'update'])->name('update');
        });

        Route::prefix('gif')->name('gif.')->group(function () {
            Route::get('/', [GifConfigController::class, 'form'])->name('form');
            Route::put('/', [GifConfigController::class, 'update'])->name('update');
        });
    });

    Route::prefix('system')->name('system.')->group(function () {
        Route::prefix('webhooks')->name('webhooks.')->middleware(SystemWebhook::class)->group(function () {
            Route::get('/', [WebhooksController::class, 'index'])->name('index');
            Route::get('create', [WebhooksController::class, 'create'])->name('create');
            Route::post('store', [WebhooksController::class, 'store'])->middleware(EnsurePasswordConfirmed::class)->name('store');
            Route::get('{webhook}', [WebhooksController::class, 'edit'])->name('edit');
            Route::put('{webhook}', [WebhooksController::class, 'update'])->middleware(EnsurePasswordConfirmed::class)->name('update');
            Route::delete('{webhook}', [WebhooksController::class, 'destroy'])->middleware(EnsurePasswordConfirmed::class)->name('delete');
            Route::delete('/', DeleteWebhooksController::class)->middleware(EnsurePasswordConfirmed::class)->name('deleteMultiple');

            Route::post('{webhook}/update-secret', UpdateWebhookSecret::class)->middleware(EnsurePasswordConfirmed::class)->name('updateSecret');

            Route::prefix('{webhook}/deliveries')->name('deliveries.')->group(function () {
                Route::get('/', [WebhookDeliveriesController::class, 'index'])->name('index');
                Route::get('show/{delivery}', [WebhookDeliveriesController::class, 'show'])->name('show');
                Route::post('resend/{delivery}', ResendWebhookController::class)->name('resend');
            });
        });

        Route::get('status', SystemStatusController::class)->name('status');

        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [SystemLogsController::class, 'index'])->name('index');
            Route::get('download', [SystemLogsController::class, 'download'])->name('download');
            Route::delete('clear', [SystemLogsController::class, 'clear'])->name('clear');
        });
    });

    Route::prefix('files')->name('files.')->group(function () {
        Route::post('upload', UploadFileController::class)->name('upload');
    });
});
