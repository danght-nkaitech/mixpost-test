<?php

namespace Inovector\Mixpost\Reports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Abstracts\Report;
use Inovector\Mixpost\Enums\InstagramInsightType;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\InstagramInsight;
use Inovector\Mixpost\Models\Metric;

class InstagramReports extends Report
{
    public function __invoke(Account $account, string $period): array
    {
        return [
            'metrics' => $this->metrics($account, $period),
            'audience' => $this->audience($account, $period),
        ];
    }

    protected function metrics(Account $account, string $period): array
    {
        $reports = InstagramInsight::account($account->id)
            ->select('type', DB::raw('SUM(value) as total'))
            ->when($period, function (Builder $query) use ($period) {
                return $this->queryPeriod($query, $period);
            })
            ->groupBy('type')
            ->get();

        $processedMetricsFromImportedPosts = Metric::account($account->id)->select(
            DB::raw('SUM(JSON_EXTRACT(data, "$.likes")) as likes'),
            DB::raw('SUM(JSON_EXTRACT(data, "$.comments")) as comments')
        )->when($period, function (Builder $query) use ($period) {
            return $this->queryPeriod($query, $period);
        })->first();

        $impressions = (int) $reports->where('type', InstagramInsightType::IMPRESSIONS)->value('total', 0);
        $followerCount = (int) $reports->where('type', InstagramInsightType::FOLLOWER_COUNT)->value('total', 0);
        $reach = (int) $reports->where('type', InstagramInsightType::REACH)->value('total', 0);

        return [
            'likes' => (int) $processedMetricsFromImportedPosts->likes ?? 0,
            'comments' => (int) $processedMetricsFromImportedPosts->comments ?? 0,
            'follower_count' => $followerCount,
            'impressions' => $impressions,
            'reach' => $reach,
        ];
    }
}
