<?php

namespace Inovector\Mixpost\Builders\Webhook;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Builders\WebhookDelivery\Filters\Status;
use Inovector\Mixpost\Contracts\Query;
use Inovector\Mixpost\Models\Webhook;

class WebhookQuery implements Query
{
    public static function apply(Request $request): Builder
    {
        $query = Webhook::query();

        if ($request->has('status') && $request->get('status') !== null) {
            $query = Status::apply($query, $request->get('status'));
        }

        return $query;
    }
}
