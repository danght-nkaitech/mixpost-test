<?php

namespace Inovector\Mixpost\Builders\WebhookDelivery;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Builders\WebhookDelivery\Filters\Status;
use Inovector\Mixpost\Contracts\Query;
use Inovector\Mixpost\Models\Webhook;

class WebhookDeliveryQuery implements Query
{
    /**
     * @throws Exception
     */
    public static function apply(Request $request): Builder
    {
        if (! $request->get('webhook')) {
            throw new Exception('Webhook model instance is required.');
        }

        if (! $request->get('webhook') instanceof Webhook) {
            throw new Exception('Webhook must be an instance of '.Webhook::class);
        }

        $query = $request->get('webhook')->deliveries()->getQuery();

        if ($request->has('status') && $request->get('status')) {
            $query = Status::apply($query, $request->get('status'));
        }

        return $query;
    }
}
