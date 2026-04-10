<?php

namespace Inovector\Mixpost\Commands\Workspace;

use Illuminate\Console\Command;
use Inovector\Mixpost\Concerns\Command\AccountsOption;
use Inovector\Mixpost\SocialProviders\Mastodon\Jobs\ProcessMastodonMetricsJob;
use Inovector\Mixpost\SocialProviders\Meta\Jobs\ProcessInstagramMetricsJob;
use Inovector\Mixpost\SocialProviders\TikTok\Jobs\ProcessTikTokMetricsJob;
use Inovector\Mixpost\SocialProviders\Twitter\Jobs\ProcessTwitterMetricsJob;

class ProcessMetrics extends Command
{
    use AccountsOption;

    public $signature = 'mixpost:process-metrics {--accounts=}';

    public $description = 'Process metrics for the social providers';

    public function handle(): int
    {
        $this->accessibleAccounts()->each(function ($account) {
            $job = match ($account->provider) {
                'twitter' => ProcessTwitterMetricsJob::class,
                'mastodon' => ProcessMastodonMetricsJob::class,
                'instagram' => ProcessInstagramMetricsJob::class,
                'tiktok' => ProcessTikTokMetricsJob::class,
                default => null,
            };

            if ($job) {
                $job::dispatch($account);
            }
        });

        return self::SUCCESS;
    }
}
