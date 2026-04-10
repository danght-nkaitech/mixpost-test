<?php

namespace Inovector\MixpostEnterprise;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Events\Account\AddingAccount;
use Inovector\Mixpost\Events\Account\StoringAccountEntities;
use Inovector\Mixpost\Events\AI\AITextGenerated;
use Inovector\Mixpost\Events\Media\UploadingMediaFile;
use Inovector\Mixpost\Events\Post\SchedulingPost;
use Inovector\Mixpost\Facades\HooksManager;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Facades\Theme as ThemeCore;
use Inovector\Mixpost\Hooks;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\WebhookManager;
use Inovector\MixpostEnterprise\Actions\Service\ServiceRetrievalAction;
use Inovector\MixpostEnterprise\Commands\DeleteUnverifiedUsers;
use Inovector\MixpostEnterprise\Commands\PublishAssetsCommand;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Inovector\MixpostEnterprise\Configs\ScriptsConfig;
use Inovector\MixpostEnterprise\Configs\SystemConfig;
use Inovector\MixpostEnterprise\Configs\ThemeConfig;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCanceled;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCreated;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionDeleted;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionPaymentFailed;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionPaymentSucceeded;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionUpdated;
use Inovector\MixpostEnterprise\Events\User\UserCreated;
use Inovector\MixpostEnterprise\Events\User\UserEmailVerified;
use Inovector\MixpostEnterprise\Events\Workspace\InvitingMember;
use Inovector\MixpostEnterprise\Events\Workspace\WorkspaceCreated;
use Inovector\MixpostEnterprise\Facades\Theme as ThemeFacade;
use Inovector\MixpostEnterprise\Http\Base\Middleware\AIUsage;
use Inovector\MixpostEnterprise\Http\Base\Middleware\EnsureEmailIsVerified;
use Inovector\MixpostEnterprise\Http\Base\Middleware\Impersonation as ImpersonationMiddleware;
use Inovector\MixpostEnterprise\Http\Base\Middleware\WorkspaceAccess;
use Inovector\MixpostEnterprise\Http\Base\Resources\UserResource;
use Inovector\MixpostEnterprise\Listeners\Account\ValidateLimitsOnAddingAccount;
use Inovector\MixpostEnterprise\Listeners\AI\RecordAIUsage;
use Inovector\MixpostEnterprise\Listeners\Media\ValidateLimitsOnUploadingMediaFile;
use Inovector\MixpostEnterprise\Listeners\Post\ValidateLimitsOnSchedulingPost;
use Inovector\MixpostEnterprise\Listeners\Subscription\ResetWorkspaceLimitsOnSubscriptionDeleted;
use Inovector\MixpostEnterprise\Listeners\Subscription\SendMailWorkspaceOwnerSubscriptionPaymentFailed;
use Inovector\MixpostEnterprise\Listeners\Subscription\SetSubscriptionPastDue;
use Inovector\MixpostEnterprise\Listeners\Subscription\SetWorkspaceLimitsOnSubscriptionCreation;
use Inovector\MixpostEnterprise\Listeners\Subscription\UpdateWorkspaceLimitsOnSubscriptionUpdate;
use Inovector\MixpostEnterprise\Listeners\User\SendUserEmailVerificationLink;
use Inovector\MixpostEnterprise\Listeners\Workspace\ValidateLimitsOnInvitingMember;
use Inovector\MixpostEnterprise\Models\Workspace;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MixpostEnterpriseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('mixpost-enterprise')
            ->hasViews()
            ->hasRoute('web/web')
            ->hasRoute('api/api')
            ->hasTranslations()
            ->hasMigrations([
                'create_mixpost-enterprise_tables',
            ])
            ->hasCommands([
                PublishAssetsCommand::class,
                DeleteUnverifiedUsers::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function (InstallCommand $command) {
                        $this->writeSeparationLine($command);
                        $command->line('Mixpost Enterprise Installation. Self-hosted social media management software.');
                        $command->line('Laravel version: '.app()->version());
                        $command->line('PHP version: '.trim(phpversion()));
                        $command->line(' ');
                        $command->line('Website: https://mixpost.app');
                        $this->writeSeparationLine($command);
                        $command->line('');

                        $command->comment('Publishing assets');
                        $command->call('mixpost-enterprise:publish-assets');
                    })
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->endWith(function (InstallCommand $command) {
                        $appUrl = config('app.url');
                        $corePath = Util::corePath(true);

                        $command->line("Visit the Mixpost Enterprise Console at $appUrl/$corePath");
                    });
            });
    }

    public function packageRegistered()
    {
        $this->app->singleton('MixpostEnterpriseTheme', function ($app) {
            return new Theme(
                new ThemeConfig($app->request)
            );
        });

        $this->app->singleton('MixpostEnterpriseScriptsConfig', function ($app) {
            return new ScriptsConfig($app->request);
        });

        $this->app->singleton('MixpostEnterpriseImpersonation', function ($app) {
            return new Impersonation($app->request);
        });

        $this->app->singleton(SystemConfig::class, function ($app) {
            return new SystemConfig($app->request);
        });

        $this->app->singleton(OnboardingConfig::class, function ($app) {
            return new OnboardingConfig($app->request);
        });

        $this->app->singleton('MixpostEnterpriseWorkspaceServiceManager', function ($app) {
            return new WorkspaceServiceManager($app);
        });

        Mixpost::globalMiddlewares([ImpersonationMiddleware::class]);
        Mixpost::webDashboardMiddlewares([EnsureEmailIsVerified::class]);
        Mixpost::workspaceMiddlewares([WorkspaceAccess::class]);
        Mixpost::aiMiddlewares([AIUsage::class]);
        Mixpost::systemWebhookEnabled(true);
    }

    public function packageBooted()
    {
        $this->bootEvents();
        $this->bootTheme();
        $this->bootBladeComponents();
        $this->bootEnterpriseConsoleUrls();
        $this->bootClassesAndActions();
        $this->bootBladePathsSripts();
        $this->bootVersion();
    }

    protected function bootEvents(): void
    {
        Event::listen(UserCreated::class, SendUserEmailVerificationLink::class);
        Event::listen(SubscriptionCreated::class, SetWorkspaceLimitsOnSubscriptionCreation::class);
        Event::listen(SubscriptionUpdated::class, UpdateWorkspaceLimitsOnSubscriptionUpdate::class);
        Event::listen(SubscriptionDeleted::class, ResetWorkspaceLimitsOnSubscriptionDeleted::class);
        Event::listen(SubscriptionPaymentFailed::class, [SetSubscriptionPastDue::class, SendMailWorkspaceOwnerSubscriptionPaymentFailed::class]);
        Event::listen(AddingAccount::class, ValidateLimitsOnAddingAccount::class);
        Event::listen(StoringAccountEntities::class, ValidateLimitsOnAddingAccount::class);
        Event::listen(SchedulingPost::class, ValidateLimitsOnSchedulingPost::class);
        Event::listen(InvitingMember::class, ValidateLimitsOnInvitingMember::class);
        Event::listen(UploadingMediaFile::class, ValidateLimitsOnUploadingMediaFile::class);
        Event::listen(AITextGenerated::class, RecordAIUsage::class);

        WebhookManager::addSystemEvent([
            UserCreated::class,
            UserEmailVerified::class,
            WorkspaceCreated::class,
            SubscriptionCreated::class,
            SubscriptionUpdated::class,
            SubscriptionCanceled::class,
            SubscriptionPaymentSucceeded::class,
            SubscriptionPaymentFailed::class,
        ]);
    }

    protected function bootTheme(): void
    {
        HooksManager::addAction(Hooks::ACTION_BEFORE_SEND_MAIL_MESSAGE, function () {
            ThemeCore::setCustomColors(ThemeFacade::configuredColors());
        });

        if (! $this->app->runningInConsole()) {
            ThemeCore::setCustomColors(ThemeFacade::configuredColors());
        }
    }

    protected function bootBladeComponents(): void
    {
        Blade::component('mixpost-enterprise::components.button.primary', 'mixpost::button-primary');
        Blade::component('mixpost-enterprise::components.form.input', 'mixpost::form-input');
        Blade::component('mixpost-enterprise::components.form.error', 'mixpost::form-error');
        Blade::component('mixpost-enterprise::components.preloader', 'mixpost::preloader');
    }

    protected function bootEnterpriseConsoleUrls(): void
    {
        if (! $this->app->runningInConsole()) {
            Route::matched(function () {
                Mixpost::enterpriseConsoleUrl(route('mixpost_e.dashboard'));
                Mixpost::stopImpersonatingUrl(route('mixpost_e.impersonate.stop'));
                Mixpost::createWorkspaceUrl(route('mixpost_e.workspace.create'));

                if (app(SystemConfig::class)->multipleWorkspacesEnabled()) {
                    Mixpost::multipleWorkspaceEnabled(true);
                }

                if (app(OnboardingConfig::class)->allowRegister()) {
                    Mixpost::registrationUrl(route('mixpost_e.register'));
                }

                if ($workspace = request()->route('workspace')) {
                    Mixpost::workspaceSettingsUrl(route('mixpost_e.workspace.settings.index', ['workspace' => $workspace]));
                    Mixpost::workspaceBillingUrl(route('mixpost_e.workspace.billing', ['workspace' => $workspace]));
                    Mixpost::workspaceUpgradeUrl(route('mixpost_e.workspace.upgrade', ['workspace' => $workspace]));
                }
            });
        }
    }

    protected function bootClassesAndActions(): void
    {
        Mixpost::customWorkspaceModel(Workspace::class);
        Mixpost::customUserResource(UserResource::class);
        Mixpost::deleteAccountRoute('mixpost_e.deleteAccount');
        Mixpost::enterpriseConfig(function () {
            return [
                'system' => SystemConfig::class,
                'onboarding' => OnboardingConfig::class,
            ];
        });

        ServiceManager::retrievalAction(new ServiceRetrievalAction);
    }

    protected function bootBladePathsSripts(): void
    {
        if (! $this->app->runningInConsole()) {
            Mixpost::bladePathHeadScripts('mixpost-enterprise::partial.headScripts');
            Mixpost::bladePathBodyScripts('mixpost-enterprise::partial.bodyScripts');
        }
    }

    protected function bootVersion(): void
    {
        if (! $this->app->runningInConsole()) {
            Route::matched(function () {
                // Show Mixpost Enterprise version only in system status page
                if (request()->routeIs('mixpost.system.status')) {
                    Mixpost::enterpriseVersion(InstalledVersions::getVersion('inovector/mixpost-enterprise'));
                }
            });
        }
    }

    protected function writeSeparationLine(InstallCommand $command): void
    {
        $command->info('*---------------------------------------------------------------------------*');
    }
}
