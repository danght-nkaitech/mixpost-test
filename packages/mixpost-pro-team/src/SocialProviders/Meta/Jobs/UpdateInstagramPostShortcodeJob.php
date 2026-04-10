<?php

namespace Inovector\Mixpost\SocialProviders\Meta\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Concerns\Job\HasSocialProviderJobRateLimit;
use Inovector\Mixpost\Concerns\Job\SocialProviderException;
use Inovector\Mixpost\Concerns\UsesSocialProviderManager;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Http\Base\Resources\AccountResource;
use Inovector\Mixpost\SocialProviders\Meta\InstagramProvider;
use Inovector\Mixpost\Support\SocialProviderResponse;

// / TODO: Integrate this Job
class UpdateInstagramPostShortcodeJob implements QueueWorkspaceAware, ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasSocialProviderJobRateLimit;
    use SocialProviderException;
    use UsesSocialProviderManager;

    public $deleteWhenMissingModels = true;

    public AccountResource $account;

    public function __construct(AccountResource $account)
    {
        $this->account = $account;
    }

    public function handle(): void
    {
        if ($this->account->isUnauthorized()) {
            return;
        }

        if ($retryAfter = $this->rateLimitExpiration()) {
            $this->release($retryAfter);

            return;
        }

        /**
         * @see InstagramProvider
         *
         * @var SocialProviderResponse $response
         */
        $response = $this->connectProvider($this->account->resource)->getPost($this->account->pivot->provider_post_id, 'shortcode');

        if ($response->isUnauthorized()) {
            $this->account->setUnauthorized();
            $this->captureException($response);

            return;
        }

        if ($response->hasExceededRateLimit()) {
            $this->storeRateLimitExceeded($response->retryAfter(), $response->isAppLevel());
            $this->release($response->retryAfter());

            return;
        }

        if ($response->rateLimitAboutToBeExceeded()) {
            $this->storeRateLimitExceeded($response->retryAfter(), $response->isAppLevel());
        }

        if ($response->hasError()) {
            $this->captureException($response);

            return;
        }

        DB::table('mixpost_post_accounts')
            ->where('account_id', $this->account->id)
            ->where('provider_post_id', $this->account->pivot->provider_post_id)
            ->update([
                'data' => [
                    'shortcode' => $response->shortcode,
                ],
            ]);
    }

    public function uniqueId(): string
    {
        return $this->account->id.$this->account->pivot->provider_post_id;
    }
}
