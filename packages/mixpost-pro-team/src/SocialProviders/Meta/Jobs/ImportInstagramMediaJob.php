<?php

namespace Inovector\Mixpost\SocialProviders\Meta\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Concerns\Job\HasSocialProviderJobRateLimit;
use Inovector\Mixpost\Concerns\Job\SocialProviderException;
use Inovector\Mixpost\Concerns\UsesSocialProviderManager;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\ImportedPost;
use Inovector\Mixpost\SocialProviders\Meta\InstagramProvider;
use Inovector\Mixpost\Support\SocialProviderResponse;

class ImportInstagramMediaJob implements QueueWorkspaceAware, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasSocialProviderJobRateLimit;
    use SocialProviderException;
    use UsesSocialProviderManager;

    public $deleteWhenMissingModels = true;

    public Account $account;

    public array $params;

    public function __construct(Account $account, array $params = [])
    {
        $this->account = $account;
        $this->params = $params;
    }

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
        $response = $this->connectProvider($this->account)->getMedia($this->params['pagination_after'] ?? '');

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

        $this->import($response->context()['data']);

        if ($after = Arr::get($response->context(), 'paging.cursors.after')) {
            ImportInstagramMediaJob::dispatch($this->account, ['pagination_after' => $after])->delay(5 * 60);
        }
    }

    protected function import(array $items): void
    {
        $data = Arr::map($items, function ($item) {
            return [
                'workspace_id' => WorkspaceManager::current()->id,
                'account_id' => $this->account->id,
                'provider_post_id' => $item['id'],
                'content' => json_encode([
                    'text' => $item['caption'] ?? '', // Reels don't have a caption
                    'media_url' => $item['media_url'] ?? ($item['thumbnail_url'] ?? ''), // Reels don't have a media url, use thumbnail_url instead
                    'is_shared_to_feed' => $item['is_shared_to_feed'] ?? false, // Reels only,
                    'media_product_type' => $item['media_product_type'],
                    'media_type' => $item['media_type'],
                    'permalink' => $item['permalink'],
                    'shortcode' => $item['shortcode'],
                    'username' => $item['username'],
                    'is_comment_enabled' => $item['is_comment_enabled'],

                ]),
                'metrics' => json_encode([
                    'like_count' => $item['like_count'],
                    'comments_count' => $item['comments_count'],
                ]),
                'created_at' => Carbon::parse($item['timestamp'], 'UTC')->toDateString(),
            ];
        });

        ImportedPost::upsert($data, ['workspace', 'account_id', 'provider_post_id'], ['content', 'metrics']);
    }
}
