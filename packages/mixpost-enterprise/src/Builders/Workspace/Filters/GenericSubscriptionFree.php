<?php

namespace Inovector\MixpostEnterprise\Builders\Workspace\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Inovector\Mixpost\Contracts\Filter;

class GenericSubscriptionFree implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->genericSubscriptionFree();
    }
}
