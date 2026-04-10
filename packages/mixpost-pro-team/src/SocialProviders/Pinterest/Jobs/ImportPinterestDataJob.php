<?php

namespace Inovector\Mixpost\SocialProviders\Pinterest\Jobs;

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
use Inovector\Mixpost\SocialProviders\Mastodon\MastodonProvider;
use Inovector\Mixpost\Support\SocialProviderResponse;

class ImportPinterestDataJob implements QueueWorkspaceAware, ShouldQueue
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
         * @see MastodonProvider
         *
         * @var SocialProviderResponse $response
         */
        $response = $this->connectProvider($this->account)->getPins($this->params['bookmark'] ?? '');

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

        $posts = $this->filterPins($response->items);

        if (empty($posts)) {
            return;
        }

        $this->import($posts);

        ImportPinterestPinAnalyticsJob::dispatch($this->account, [
            'start_date' => Carbon::parse(Arr::last($posts)['created_at'], 'UTC')->toDateString(),
            'end_date' => Carbon::parse(Arr::first($posts)['created_at'], 'UTC')->toDateString(),
        ]);

        if ($bookmark = $response->bookmark ?? '') {
            ImportPinterestDataJob::dispatch($this->account, ['bookmark' => $bookmark])->delay(5 * 60);
        }
    }

    protected function filterPins(array $data): array
    {
        return Arr::where($data, function ($item) {
            return Carbon::parse($item['created_at'], 'UTC')->greaterThan(Carbon::now('UTC')->subDays(90));
        });
    }

    protected function import(array $items): void
    {
        $data = Arr::map($items, function ($item) {
            return [
                'workspace_id' => WorkspaceManager::current()->id,
                'account_id' => $this->account->id,
                'provider_post_id' => $item['id'],
                'content' => json_encode([
                    'title' => $item['title'],
                    'text' => $item['description'],
                    'link' => $item['link'],
                    'board_id' => $item['board_id'],
                    'media' => $item['media'],
                ]),
                'metrics' => json_encode([]),
                'created_at' => Carbon::parse($item['created_at'], 'UTC')->toDateTimeString(),
            ];
        });

        ImportedPost::upsert($data, ['workspace_id', 'account_id', 'provider_post_id'], ['content']);
    }
}
