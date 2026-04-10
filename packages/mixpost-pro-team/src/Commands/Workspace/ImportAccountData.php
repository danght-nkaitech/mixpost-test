<?php

namespace Inovector\Mixpost\Commands\Workspace;

use Illuminate\Console\Command;
use Inovector\Mixpost\Concerns\Command\AccountsOption;
use Inovector\Mixpost\SocialProviders\Mastodon\Jobs\ImportMastodonPostsJob;
use Inovector\Mixpost\SocialProviders\Meta\Jobs\ImportFacebookInsightsJob;
use Inovector\Mixpost\SocialProviders\Meta\Jobs\ImportInstagramInsightsJob;
use Inovector\Mixpost\SocialProviders\Meta\Jobs\ImportInstagramMediaJob;
use Inovector\Mixpost\SocialProviders\Pinterest\Jobs\ImportPinterestDataJob;
use Inovector\Mixpost\SocialProviders\TikTok\Jobs\ImportTikTokVideosJob;
use Inovector\Mixpost\SocialProviders\Twitter\Jobs\ImportTwitterPostsJob;

class ImportAccountData extends Command
{
    use AccountsOption;

    public $signature = 'mixpost:import-account-data {--accounts=}';

    public $description = 'Import data from social service providers';

    public function handle(): int
    {
        $this->accessibleAccounts()->each(function ($account) {
            // TODO:: Move provider jobs to provider class
            // example: $account->provider()->importDataJobs();
            $jobs = match ($account->provider) {
                'twitter' => [
                    ImportTwitterPostsJob::class,
                ],
                'facebook_page' => [
                    ImportFacebookInsightsJob::class,
                    //                    ImportFacebookPagePostsJob::class,
                ],
                'instagram' => [
                    ImportInstagramInsightsJob::class,
                    ImportInstagramMediaJob::class,
                ],
                'mastodon' => [
                    ImportMastodonPostsJob::class,
                ],
                'pinterest' => [
                    ImportPinterestDataJob::class,
                ],
                'tiktok' => [
                    ImportTikTokVideosJob::class,
                ],
                default => [],
            };

            foreach ($jobs as $job) {
                $job::dispatch($account);
            }
        });

        return self::SUCCESS;
    }
}
