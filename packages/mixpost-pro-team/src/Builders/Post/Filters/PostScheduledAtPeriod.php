<?php

namespace Inovector\Mixpost\Builders\Post\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Inovector\Mixpost\Contracts\Filter;

class PostScheduledAtPeriod implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->whereDate('scheduled_at', '>=', $value['scheduled_at_period_start'])
            ->whereDate('scheduled_at', '<=', $value['scheduled_at_period_end']);
    }
}
