<?php

namespace Inovector\Mixpost\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Models\Account;

class UnauthorizeMastodonServiceAccountsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $serverName
    ) {}

    public function handle(): void
    {
        Account::withoutWorkspace()
            ->where('provider', 'mastodon')
            ->where('data->server', $this->serverName)
            ->each(function (Account $account) {
                $account->setUnauthorized();
            });
    }
}
