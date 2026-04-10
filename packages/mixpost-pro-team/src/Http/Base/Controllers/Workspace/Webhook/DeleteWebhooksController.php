<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Webhook;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Models\Webhook;

class DeleteWebhooksController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $delete = Webhook::whereIn('uuid', $request->input('webhooks'))->delete();

        if (! $delete) {
            return redirect()
                ->route('mixpost.webhooks.index', ['workspace' => $request->route('workspace')])
                ->with('error', __('mixpost::webhook.delete_webhooks_failed'));
        }

        return redirect()
            ->route('mixpost.webhooks.index', ['workspace' => $request->route('workspace')])
            ->with('success', __('mixpost::webhook.delete_webhooks_success'));
    }
}
