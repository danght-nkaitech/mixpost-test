<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Http\Base\Middleware\CheckWorkspaceUser;
use Inovector\Mixpost\Http\Base\Middleware\IdentifyWorkspace;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\BillingController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\CancelInvitationController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\CancelSubscriptionController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\ChangeSubscriptionPlanController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\DetachUserController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\DowngradeSubscriptionToFreePlanController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\DownloadReceiptController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\InviteMemberController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\MembersController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\NewSubscriptionController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\PortalPaymentPlatformController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\ReceiptsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\ResumeSubscriptionController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\SecurityController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\ServicesController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\SettingsController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\SubscriptionInfoController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\UpdateUserRoleController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\UpgradeController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\WorkspaceLockedController;
use Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace\WorkspaceTrialEndedController;
use Inovector\MixpostEnterprise\Http\Base\Middleware\WorkspaceAccess;
use Inovector\MixpostEnterprise\Http\Base\Middleware\WorkspaceOwner;

Route::middleware([
    IdentifyWorkspace::class,
    CheckWorkspaceUser::class.':'.WorkspaceUserRole::ADMIN->name,
])->prefix('{workspace}')
    ->name('workspace.')
    ->group(function () {
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::put('/', [SettingsController::class, 'update'])->name('update');
        });

        Route::get('members', MembersController::class)->middleware(WorkspaceAccess::class)->name('members');

        Route::prefix('invitations')
            ->name('invitations.')
            ->middleware(WorkspaceAccess::class)->group(function () {
                Route::post('/', InviteMemberController::class)->name('invite');
                //              Route::put('{invitation}', UpdateInvitationController::class)->name('update');
                Route::delete('{invitation}', CancelInvitationController::class)->name('cancel');
            });

        Route::prefix('users')->name('users.')->group(function () {
            Route::put('{user}', UpdateUserRoleController::class)->name('updateRole');
            Route::delete('{user}', DetachUserController::class)->name('detach');
        });

        Route::get('billing', BillingController::class)->name('billing');
        Route::put('portal-payment-platform', PortalPaymentPlatformController::class)->name('portalPaymentPlatform');
        Route::get('upgrade', UpgradeController::class)->name('upgrade');
        Route::get('trial-ended', WorkspaceTrialEndedController::class)->name('trialEnded');
        Route::get('locked', WorkspaceLockedController::class)->name('locked');

        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::post('new', NewSubscriptionController::class)->name('new');
            Route::get('info', SubscriptionInfoController::class)->name('info');
            Route::post('change-plan', ChangeSubscriptionPlanController::class)->name('changePlan');
            Route::post('downgrade-to-free-plan', DowngradeSubscriptionToFreePlanController::class)->name('downgradeToFreePlan');
            Route::post('cancel', CancelSubscriptionController::class)->name('cancel');
            Route::post('resume', ResumeSubscriptionController::class)->name('resume');
        });

        Route::prefix('receipts')->name('receipts.')->group(function () {
            Route::get('', ReceiptsController::class)->name('index');
            Route::get('{receipt}', DownloadReceiptController::class)->name('download');
        });

        Route::prefix('workspace-services')->name('services.')->group(function () {
            Route::get('/', [ServicesController::class, 'index'])->name('index');
            Route::put('{service}', [ServicesController::class, 'update'])->name('update');
        });

        Route::middleware([WorkspaceOwner::class])
            ->prefix('security')
            ->name('security.')
            ->group(function () {
                Route::get('/', [SecurityController::class, 'index'])->name('index');
                Route::delete('/', [SecurityController::class, 'destroy'])->name('delete');
            });
    });
