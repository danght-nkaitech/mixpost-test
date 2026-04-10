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
use Illuminate\Support\LazyCollection;
use Inovector\Mixpost\Concerns\Job\HasSocialProviderJobRateLimit;
use Inovector\Mixpost\Concerns\Job\SocialProviderException;
use Inovector\Mixpost\Concerns\UsesSocialProviderManager;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Enums\PinterestMetricType;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\ImportedPost;
use Inovector\Mixpost\Models\PinterestAnalytic;
use Inovector\Mixpost\SocialProviders\Pinterest\PinterestProvider;
use Inovector\Mixpost\Support\SocialProviderResponse;

class ImportPinterestPinAnalyticsJob implements QueueWorkspaceAware, ShouldQueue
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

        if ($retryAfter = $this->rateLimitExpiration()) {
            $this->release($retryAfter);

            return;
        }

        if ($this->account->getProviderClass()::isSandbox()) {
            return;
        }

        $connection = $this->connectProvider($this->account);
        $posts = [];

        /**
         * @var ImportedPost $post
         */
        foreach ($this->posts() as $post) {
            /**
             * @see PinterestProvider
             *
             * @var SocialProviderResponse $response
             */
            $response = $connection->getPinAnalytics($post->provider_post_id, [
                'start_date' => $post->created_at->toDateString(),
                'end_date' => Carbon::today('UTC')->toDateString(),
                'metric_types' => implode(',', ['OUTBOUND_CLICK', 'PIN_CLICK', 'IMPRESSION', 'SAVE', 'SAVE_RATE']),
            ]);

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

            $post->setAttribute('metrics', [
                'outbound_click' => (int) Arr::get($response->all, 'summary_metrics.OUTBOUND_CLICK', 0),
                'impressions' => (int) Arr::get($response->all, 'summary_metrics.IMPRESSION', 0),
                'save_rate' => (int) Arr::get($response->all, 'summary_metrics.SAVE_RATE', 0),
                'click' => (int) Arr::get($response->all, 'summary_metrics.PIN_CLICK', 0),
                'save' => (int) Arr::get($response->all, 'summary_metrics.SAVE', 0),
            ]);

            $posts[] = $post;

            $this->insertAnalytics($post->provider_post_id, $response->all['daily_metrics']);
        }

        $this->save($posts);
    }

    protected function posts(): LazyCollection
    {
        return ImportedPost::where('account_id', $this->account->id)
            ->whereDate('created_at', '>=', $this->params['start_date'])
            ->whereDate('created_at', '<=', $this->params['end_date'])
            ->latest()
            ->cursor();
    }

    protected function save(array $items): void
    {
        $data = Arr::map($items, function (ImportedPost $item) {
            return [
                'workspace_id' => WorkspaceManager::current()->id,
                'account_id' => $this->account->id,
                'provider_post_id' => $item->provider_post_id,
                'content' => json_encode($item->content),
                'metrics' => json_encode($item->metrics ?? []),
                'created_at' => $item->created_at,
            ];
        });

        ImportedPost::upsert($data, ['workspace_id', 'account_id', 'provider_post_id'], ['content', 'metrics']);
    }

    protected function insertAnalytics(string $providerPostId, array $items): void
    {
        $data = Arr::map($items, function ($item) use ($providerPostId) {
            return [
                'workspace_id' => WorkspaceManager::current()->id,
                'account_id' => $this->account->id,
                'provider_post_id' => $providerPostId,
                'date' => $item['date'],
                'metrics' => json_encode([
                    PinterestMetricType::SAVE_RATE->value => (int) Arr::get($item['metrics'], 'SAVE_RATE', 0),
                    PinterestMetricType::PIN_CLICK->value => (int) Arr::get($item['metrics'], 'PIN_CLICK', 0),
                    PinterestMetricType::IMPRESSION->value => (int) Arr::get($item['metrics'], 'IMPRESSION', 0),
                    PinterestMetricType::OUTBOUND_CLICK->value => (int) Arr::get($item['metrics'], 'OUTBOUND_CLICK', 0),
                    PinterestMetricType::SAVE->value => (int) Arr::get($item['metrics'], 'SAVE', 0),
                ]),
            ];
        });

        PinterestAnalytic::upsert($data, ['workspace_id', 'account_id', 'provider_post_id', 'date'], ['metrics']);
    }
}
