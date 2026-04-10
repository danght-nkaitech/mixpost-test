<?php

namespace Inovector\MixpostEnterprise\Builders\Workspace\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Inovector\Mixpost\Contracts\Filter;

class SubscriptionStatus implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->whereHas('subscriptions', function ($query) use ($value) {
            $query->where('status', $value);
        });
    }
}
