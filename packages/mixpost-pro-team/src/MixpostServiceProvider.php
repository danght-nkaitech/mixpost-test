<?php

namespace Inovector\Mixpost;

use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Inovector\Mixpost\Abstracts\User as UserAbstract;
use Inovector\Mixpost\Actions\Common\MakeQueueWorkspaceAware;
use Inovector\Mixpost\Commands\CleanupMultipartUploadsCommand;
use Inovector\Mixpost\Commands\ClearServicesCache;
use Inovector\Mixpost\Commands\ClearSettingsCache;
use Inovector\Mixpost\Commands\CreateAdmin;
use Inovector\Mixpost\Commands\CreateMastodonApp;
use Inovector\Mixpost\Commands\GenerateBlueskyPrivateKey;
use Inovector\Mixpost\Commands\GeneratePageSamples;
use Inovector\Mixpost\Commands\PruneTemporaryDirectory;
use Inovector\Mixpost\Commands\PublishAssetsCommand;
use Inovector\Mixpost\Commands\RunWorkspaceCommand;
use Inovector\Mixpost\Commands\SetupMultipartUploadsCleanupCommand;
use Inovector\Mixpost\Commands\Workspace\CheckAndRefreshAccountToken;
use Inovector\Mixpost\Commands\Workspace\ImportAccountAudience;
use Inovector\Mixpost\Commands\Workspace\ImportAccountData;
use Inovector\Mixpost\Commands\Workspace\ProcessMetrics;
use Inovector\Mixpost\Commands\Workspace\PruneTrashedPosts;
use Inovector\Mixpost\Commands\Workspace\RunScheduledPosts;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Configs\ThemeConfig;
use Inovector\Mixpost\Events\Account\AccountAdded;
use Inovector\Mixpost\Events\Account\AccountUnauthorized;
use Inovector\Mixpost\Events\Post\PostActivityCreated;
use Inovector\Mixpost\Events\Post\PostCreated;
use Inovector\Mixpost\Events\Post\PostDeleteFromSocialPlatforms;
use Inovector\Mixpost\Events\Post\PostPublished;
use Inovector\Mixpost\Events\Post\PostPublishedFailed;
use Inovector\Mixpost\Events\Post\PostScheduleAtUpdated;
use Inovector\Mixpost\Events\Post\PostScheduled;
use Inovector\Mixpost\Events\Post\PostScheduleProcessing;
use Inovector\Mixpost\Events\Post\PostSetDraft;
use Inovector\Mixpost\Exceptions\MixpostExceptionHandler;
use Inovector\Mixpost\Guards\AccessTokenGuard;
use Inovector\Mixpost\Listeners\Account\HandleAccountImports;
use Inovector\Mixpost\Listeners\Account\SendAccountUnauthorizedNotification;
use Inovector\Mixpost\Listeners\HandleSystemWebhookEvent;
use Inovector\Mixpost\Listeners\HandleWorkspaceWebhookEvent;
use Inovector\Mixpost\Listeners\Post\HandlePostActivityCreatedEvent;
use Inovector\Mixpost\Listeners\Post\HandlePostDeleteFromSocialPlatformsEvent;
use Inovector\Mixpost\Listeners\Post\LogPostCreatedActivity;
use Inovector\Mixpost\Listeners\Post\LogPostPublishedActivity;
use Inovector\Mixpost\Listeners\Post\LogPostPublishedFailedActivity;
use Inovector\Mixpost\Listeners\Post\LogPostScheduledActivity;
use Inovector\Mixpost\Listeners\Post\LogPostScheduleProcessingActivity;
use Inovector\Mixpost\Listeners\Post\LogPostSetDraftActivity;
use Inovector\Mixpost\Listeners\Post\LogPostUpdatedScheduledTimeActivity;
use Inovector\Mixpost\Listeners\Post\SubscribePostToActivitiesNotification;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MixpostServiceProvider extends PackageServiceProvider
{
    use UsesUserModel;

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('mixpost')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web/web')
            ->hasRoute('api/api')
            ->hasRoute('broadcast/channels')
            ->hasTranslations()
            ->hasMigrations([
                'create_mixpost_tables',
            ])
            ->hasCommands([
                PublishAssetsCommand::class,
                RunWorkspaceCommand::class,
                CreateMastodonApp::class,
                ClearSettingsCache::class,
                ClearServicesCache::class,
                RunScheduledPosts::class,
                ImportAccountAudience::class,
                ImportAccountData::class,
                CheckAndRefreshAccountToken::class,
                ProcessMetrics::class,
                PruneTrashedPosts::class,
                PruneTemporaryDirectory::class,
                CleanupMultipartUploadsCommand::class,
                SetupMultipartUploadsCleanupCommand::class,
                CreateAdmin::class,
                GeneratePageSamples::class,
                GenerateBlueskyPrivateKey::class,
            ])->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function (InstallCommand $command) {
                        $this->writeSeparationLine($command);
                        $command->line('Mixpost Installation. Self-hosted social media management software.');
                        $command->line('Laravel version: '.app()->version());
                        $command->line('PHP version: '.trim(phpversion()));
                        $command->line(' ');
                        $command->line('Website: https://mixpost.app');
                        $this->writeSeparationLine($command);
                        $command->line('');

                        $command->comment('Publishing assets');
                        $command->call('mixpost:publish-assets');
                    })
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->endWith(function (InstallCommand $command) {
                        $hasUsers = self::getUserClass()::exists();

                        if (! $hasUsers) {
                            $appUrl = config('app.url');
                            $corePath = config('mixpost.core_path', 'mixpost');

                            $command->line("Visit the Mixpost dashboard at $appUrl/$corePath");
                        }

                        if ($hasUsers) {
                            $command->call('mixpost:create-admin');
                        }
                    });
            });
    }

    public function packageRegistered(): void
    {
        config([
            'auth.guards.mixpost_access_token' => array_merge([
                'driver' => 'mixpost_access_token',
                'provider' => null,
            ], config('auth.guards.mixpost_access_token', [])),
        ]);

        $this->app->singleton('MixpostHooksManager', function () {
            return new HooksManager;
        });

        $this->app->singleton('MixpostWorkspaceManager', function () {
            return new WorkspaceManager;
        });

        $this->app->singleton('MixpostSocialProviderManager', function ($app) {
            return new SocialProviderManager($app);
        });

        $this->app->singleton('MixpostAIManager', function () {
            return new AIManager;
        });

        $this->app->singleton('MixpostUrlShortenerManager', function () {
            return new UrlShortenerManager;
        });

        $this->app->singleton('MixpostSettings', function ($app) {
            return new Settings($app);
        });

        $this->app->singleton('MixpostServiceManager', function ($app) {
            return new ServiceManager($app);
        });

        $this->app->singleton('MixpostTheme', function ($app) {
            return new Theme(
                new ThemeConfig($app->request)
            );
        });
    }

    public function packageBooted(): void
    {
        $this->bootEvents();

        $this->checkModelInstances();

        $this->configureQueue();

        $this->configureGuard();

        $this->registerExceptionHandler();

        Gate::define('viewMixpost', function () {
            return true;
        });
    }

    protected function bootEvents(): void
    {
        Event::listen(WebhookManager::systemEvents(), HandleSystemWebhookEvent::class);
        Event::listen(WebhookManager::workspaceEvents(), HandleWorkspaceWebhookEvent::class);

        Event::listen(PostDeleteFromSocialPlatforms::class, HandlePostDeleteFromSocialPlatformsEvent::class);

        Event::listen(AccountAdded::class, HandleAccountImports::class);
        Event::listen(AccountUnauthorized::class, SendAccountUnauthorizedNotification::class);
        Event::listen(PostActivityCreated::class, HandlePostActivityCreatedEvent::class);
        Event::listen(PostCreated::class, SubscribePostToActivitiesNotification::class);
        Event::listen(PostCreated::class, LogPostCreatedActivity::class);
        Event::listen(PostSetDraft::class, LogPostSetDraftActivity::class);
        Event::listen(PostScheduled::class, LogPostScheduledActivity::class);
        Event::listen(PostScheduleAtUpdated::class, LogPostUpdatedScheduledTimeActivity::class);
        Event::listen(PostScheduleProcessing::class, LogPostScheduleProcessingActivity::class);
        Event::listen(PostPublished::class, LogPostPublishedActivity::class);
        Event::listen(PostPublishedFailed::class, LogPostPublishedFailedActivity::class);
    }

    protected function checkModelInstances(): void
    {
        $userModel = $this->app->make(config('mixpost.user_model'));

        if (! $userModel instanceof UserAbstract) {
            throw new \Exception('The user model must be an instance of Inovector\Mixpost\Abstracts\User');
        }
    }

    protected function configureQueue(): void
    {
        app(MakeQueueWorkspaceAware::class)();
    }

    protected function configureGuard(): void
    {
        Auth::resolved(function ($auth) {
            $auth->extend('mixpost_access_token', function ($app, $name, array $config) use ($auth) {
                return tap($this->createGuard($auth, $config), function ($guard) {
                    $this->app->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    protected function createGuard($auth, $config): RequestGuard
    {
        return new RequestGuard(
            new AccessTokenGuard($auth),
            request(),
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }

    protected function registerExceptionHandler(): void
    {
        app()->bind(ExceptionHandler::class, MixpostExceptionHandler::class);
    }

    protected function writeSeparationLine(InstallCommand $command): void
    {
        $command->info('*---------------------------------------------------------------------------*');
    }
}
