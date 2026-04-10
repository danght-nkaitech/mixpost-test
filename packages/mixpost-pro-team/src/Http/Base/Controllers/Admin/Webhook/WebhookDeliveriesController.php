<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Webhook;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Builders\WebhookDelivery\WebhookDeliveryQuery;
use Inovector\Mixpost\Http\Base\Resources\WebhookDeliveryResource;
use Inovector\Mixpost\Http\Base\Resources\WebhookResource;
use Inovector\Mixpost\Models\Webhook;

class WebhookDeliveriesController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|Response
    {
        $webhook = Webhook::firstOrFailByUuid($request->route('webhook'));

        if (! $webhook->isSystem()) {
            abort(404);
        }

        $request->query->set('webhook', $webhook);

        $deliveries = WebhookDeliveryQuery::apply($request)
            ->latest('id')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Admin/Webhooks/WebhookDeliveries', [
            'filter' => [
                'status' => $request->query('status'),
            ],
            'webhook' => fn () => (new WebhookResource($webhook))->resolve(),
            'deliveries' => fn () => WebhookDeliveryResource::collection($deliveries)->except(['payload', 'response']),
        ]);
    }

    public function show(Request $request): WebhookDeliveryResource
    {
        $webhook = Webhook::firstOrFailByUuid($request->route('webhook'));

        if (! $webhook->isSystem()) {
            abort(404);
        }

        $delivery = $webhook->deliveries()->where('uuid', $request->route('delivery'))->firstOrFail();

        return new WebhookDeliveryResource($delivery);
    }
}
