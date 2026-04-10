<?php

namespace Inovector\Mixpost\Builders\Workspace\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Contracts\Filter;

class Exclude implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->whereNotIn('uuid', Arr::wrap($value));
    }
}
