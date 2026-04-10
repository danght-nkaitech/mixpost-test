<?php

namespace Inovector\Mixpost\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;

interface Filter
{
    public static function apply(Builder $builder, $value): Builder;
}
