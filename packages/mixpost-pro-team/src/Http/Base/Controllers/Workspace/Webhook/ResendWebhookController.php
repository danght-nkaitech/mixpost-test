<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Webhook;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Models\Webhook;
use Inovector\Mixpost\Models\WebhookDelivery;

class ResendWebhookController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $webhook = Webhook::firstOrFailByUuid($request->route('webhook'));

        /** @var WebhookDelivery $delivery */
        $delivery = $webhook->deliveries()->where('uuid', $request->route('delivery'))->firstOrFail();

        $delivery->setRelation('webhook', $webhook);

        $delivery->resend();

        $delivery->setAsResentManually();

        return redirect()->back()->with('success', __('mixpost::webhook.resent'));
    }
}
