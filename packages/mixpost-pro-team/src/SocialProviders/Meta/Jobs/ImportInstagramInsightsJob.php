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
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\Job\HasSocialProviderJobRateLimit;
use Inovector\Mixpost\Concerns\Job\SocialProviderException;
use Inovector\Mixpost\Concerns\UsesSocialProviderManager;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Enums\InstagramInsightType;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\InstagramInsight;
use Inovector\Mixpost\SocialProviders\Meta\InstagramProvider;
use Inovector\Mixpost\Support\SocialProviderResponse;

class ImportInstagramInsightsJob implements QueueWorkspaceAware, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasSocialProviderJobRateLimit;
    use SocialProviderException;
    use UsesSocialProviderManager;

    public $deleteWhenMissingModels = true;

    public Account $account;

    public string $metricType;

    public function __construct(Account $account, string $metricType = 'time_series')
    {
        $this->account = $account;
        $this->metricType = $metricType;
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
        $response = match ($this->metricType) {
            'time_series' => $this->connectProvider($this->account)->getInsightsTimeSeries(),
            'total_value' => $this->connectProvider($this->account)->getInsightsTotalValue(),
        };

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

        $insights = Arr::get($response->context(), 'data', []);

        foreach ($insights as $insight) {
            if ($this->metricType === 'time_series') {
                $this->importInsightsTimeSeries(InstagramInsightType::fromName(Str::upper($insight['name'])), $insight['values']);
            }

            //            if ($this->metricType === 'total_value') {
            //                $this->importInsightsTotalValue(InstagramInsightType::fromName(Str::upper($insight['name'])), $insight['total_value']);
            //            }
        }

        //        ImportInstagramInsightsJob::dispatch($this->account, 'total_value')->delay(60 * 60); // 1 hour
    }

    protected function importInsightsTimeSeries(InstagramInsightType $type, array $items): void
    {
        $data = Arr::map($items, function ($item) use ($type) {
            return [
                'workspace_id' => WorkspaceManager::current()->id,
                'account_id' => $this->account->id,
                'type' => $type,
                'date' => Carbon::parse($item['end_time'])->toDateString(),
                'value' => $item['value'],
            ];
        });

        InstagramInsight::upsert($data, ['workspace_id', 'account_id', 'type', 'date'], ['value']);
    }

    protected function importInsightsTotalValue(InstagramInsightType $type, array $items): void
    {
        $data = Arr::map($items, function ($item) use ($type) {
            return [
                'workspace_id' => WorkspaceManager::current()->id,
                'account_id' => $this->account->id,
                'type' => $type,
                'date' => Carbon::today('UTC')->toDateString(),
                'value' => $item['value'],
            ];
        });

        InstagramInsight::upsert($data, ['workspace_id', 'account_id', 'type', 'date'], ['value']);
    }
}
