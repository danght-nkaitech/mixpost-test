<?php

namespace Inovector\Mixpost\Jobs\Post;

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
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\SocialProviders\Meta\InstagramProvider;
use Inovector\Mixpost\Support\SocialProviderResponse;

class DeletePostFromSocialPlatformJob implements QueueWorkspaceAware, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasSocialProviderJobRateLimit;
    use SocialProviderException;
    use UsesSocialProviderManager;

    public $deleteWhenMissingModels = true;

    public function __construct(public readonly Account $account,
        public readonly string $providerPostId,
        public readonly array $data,
        public readonly Post $post,
        public readonly int $userId) {}

    public function handle(): void
    {
        if ($this->account->isUnauthorized()) {
            return;
        }

        if (! $this->account->isServiceActive()) {
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
        $response = $this->connectProvider($this->account)->deletePost($this->providerPostId, $this->data);

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

        $this->account
            ->posts()
            ->withTrashed()
            ->where('provider_post_id', $this->providerPostId)
            ->update([
                'provider_post_id' => null,
                'data' => null,
            ]);

        $this->post->logDeletedFromSocialPlatformActivity($this->userId, [
            'provider' => $this->account->provider,
            'provider_post_id' => $this->providerPostId,
        ]);
    }
}
