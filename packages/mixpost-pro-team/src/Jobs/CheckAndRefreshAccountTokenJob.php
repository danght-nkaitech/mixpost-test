<?php

namespace Inovector\Mixpost\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Concerns\Job\HasSocialProviderJobRateLimit;
use Inovector\Mixpost\Concerns\Job\SocialProviderException;
use Inovector\Mixpost\Concerns\UsesSocialProviderManager;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Support\SocialProviderResponse;

class CheckAndRefreshAccountTokenJob implements QueueWorkspaceAware, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasSocialProviderJobRateLimit;
    use SocialProviderException;
    use UsesSocialProviderManager;

    public $deleteWhenMissingModels = true;

    public Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle(): void
    {
        if ($this->account->isUnauthorized()) {
            return;
        }

        if (! $this->account->isServiceActive()) {
            return;
        }

        $connection = $this->connectProvider($this->account);

        if (! $connection->hasRefreshToken()) {
            return;
        }

        if (! method_exists($connection, 'refreshToken')) {
            return;
        }

        if (! $connection->tokenIsAboutToExpire()) {
            return;
        }

        /**
         * @var SocialProviderResponse $response
         **/
        $response = $connection->refreshToken();

        if ($response->isUnauthorized()) {
            $this->account->setUnauthorized();

            return;
        }

        if ($response->hasError()) {
            $this->captureException($response);

            return;
        }

        $this->account->updateAccessToken(
            array_merge(
                $this->account->access_token->toArray(),
                $response->context()
            )
        );
    }
}
