<?php

namespace Inovector\Mixpost\Builders\WebhookDelivery\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Inovector\Mixpost\Contracts\Filter;
use Inovector\Mixpost\Enums\WebhookDeliveryStatus;

class Status implements Filter
{
    public static function apply(Builder $builder, $value): Builder
    {
        $status = match ($value) {
            'success' => WebhookDeliveryStatus::SUCCESS,
            'error' => WebhookDeliveryStatus::ERROR,
            default => null
        };

        if ($status === null) {
            return $builder;
        }

        return $builder->where('status', $status);
    }
}
