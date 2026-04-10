<?php

namespace Inovector\MixpostEnterprise\Metrics;

use Flowframe\Trend\Trend;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Models\User;
use Inovector\MixpostEnterprise\Abstracts\Metric;

class Users extends Metric
{
    public function calculate(Request $request): Collection
    {
        $ranges = $this->dateRanges();

        return $this->mapValuesToReadableDates(
            Trend::model(User::class)
                ->between(
                    start: $ranges[$request->get('days')] ?? $ranges[array_key_first($ranges)],
                    end: Carbon::now()->endOfDay(),
                )
                ->perDay()
                ->count()
        );
    }
}
