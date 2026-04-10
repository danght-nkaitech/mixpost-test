<?php

namespace Inovector\Mixpost\Commands\Workspace;

use Illuminate\Console\Command;
use Inovector\Mixpost\Concerns\Command\AccountsOption;
use Inovector\Mixpost\Jobs\CheckAndRefreshAccountTokenJob;

class CheckAndRefreshAccountToken extends Command
{
    use AccountsOption;

    public $signature = 'mixpost:check-and-refresh-account-token {--accounts=}';

    public $description = 'Check and refresh social account token';

    protected function providers(): array
    {
        return [
            'youtube',
            'gbp',
            'linkedin',
            'pinterest',
            'tiktok',
            'threads',
            'bluesky',
        ];
    }

    public function handle(): int
    {
        $this->accessibleAccounts()
            ->whereIn('provider', $this->providers())
            ->each(function ($account) {
                CheckAndRefreshAccountTokenJob::dispatch($account);
            });

        return self::SUCCESS;
    }
}
