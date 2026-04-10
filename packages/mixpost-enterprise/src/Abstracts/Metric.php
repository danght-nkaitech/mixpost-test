<?php

namespace Inovector\MixpostEnterprise\Abstracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Facades\Settings;
use Inovector\MixpostEnterprise\Contracts\Metric as MetricContract;
use Inovector\MixpostEnterprise\Util;

abstract class Metric implements MetricContract
{
    use UsesAuth;

    protected string $timezone = 'UTC';

    public static function make(): static
    {
        return new static;
    }

    public function timezone(): string
    {
        if ($this->timezone) {
            return $this->timezone;
        }

        return $this->timezone = Settings::get('timezone');
    }

    public function dateRanges(): array
    {
        return [
            10 => Carbon::now()->tz($this->timezone())->subDays(10)->startOfDay(),
            30 => Carbon::now()->tz($this->timezone())->subDays(30)->startOfDay(),
            60 => Carbon::now()->tz($this->timezone())->subDays(60)->startOfDay(),
            90 => Carbon::now()->tz($this->timezone())->subDays(90)->startOfDay(),
        ];
    }

    public function mapValuesToReadableDates(Collection $collection): Collection
    {
        return $collection->map(function ($value) {
            return [
                'date' => $value->date,
                'date_readable' => Util::dateFormat(Carbon::parse($value->date)),
                'aggregate' => $value->aggregate,
            ];
        });
    }
}
