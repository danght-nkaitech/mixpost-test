<?php

namespace Inovector\MixpostEnterprise\Builders\Workspace\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Contracts\Filter;

class AccessStatus implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->whereIn('access_status', Arr::wrap($value));
    }
}
