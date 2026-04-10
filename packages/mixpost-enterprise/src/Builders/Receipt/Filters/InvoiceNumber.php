<?php

namespace Inovector\MixpostEnterprise\Builders\Receipt\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Inovector\Mixpost\Contracts\Filter;

class InvoiceNumber implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->where('invoice_number', 'like', "%{$value}%");
    }
}
