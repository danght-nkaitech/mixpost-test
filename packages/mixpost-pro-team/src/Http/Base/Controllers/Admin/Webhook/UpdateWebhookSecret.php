<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Webhook;

use Illuminate\Http\RedirectResponse;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdateWebhookSecret as UpdateWebhookSecretRequest;

class UpdateWebhookSecret
{
    public function __invoke(UpdateWebhookSecretRequest $request): RedirectResponse
    {
        $update = $request->handle();

        if ($update) {
            return redirect()->back()->with('success', __('mixpost::webhook.secret_updated'));
        }

        return redirect()->back()->with('error', __('mixpost::error.try_again'));
    }
}
