<?php

namespace Inovector\Mixpost\Builders\Media\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Contracts\Filter;
use Inovector\Mixpost\Util;

class MimeTypes implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        $value = array_intersect(Arr::wrap($value), Util::config('mime_types'));

        return $builder->when($value, function ($query) use ($value) {
            $query->whereIn('mime_type', Arr::wrap($value));
        });
    }
}
