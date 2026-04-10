<?php

namespace Inovector\MixpostEnterprise\Metrics;

use Flowframe\Trend\Trend;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inovector\MixpostEnterprise\Abstracts\Metric;
use Inovector\MixpostEnterprise\Models\Subscription;

class Subscriptions extends Metric
{
    public function calculate(Request $request): Collection
    {
        $ranges = $this->dateRanges();

        return $this->mapValuesToReadableDates(
            Trend::model(Subscription::class)
                ->between(
                    start: $ranges[$request->get('days')] ?? $ranges[array_key_first($ranges)],
                    end: Carbon::now()->endOfDay(),
                )
                ->perDay()
                ->count()
        );
    }
}
