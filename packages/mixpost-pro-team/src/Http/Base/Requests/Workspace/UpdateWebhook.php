<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Webhook;

class UpdateWebhook extends WebhookFormRequest
{
    public function handle(): int
    {
        $record = Webhook::firstOrFailByUuid($this->route('webhook'));

        if (WorkspaceManager::current() && ! $record->isForWorkspace(WorkspaceManager::current())) {
            abort(404);
        }

        return $record->update([
            'name' => $this->input('name'),
            'callback_url' => $this->input('callback_url'),
            'method' => $this->input('method', 'post'),
            'content_type' => $this->input('content_type', 'application/json'),
            'max_attempts' => $this->input('max_attempts', 0),
            'events' => $this->input('events'),
            'active' => $this->input('active', false),
        ]);
    }
}
