<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Webhook;

class StoreWebhook extends WebhookFormRequest
{
    public function handle(): Webhook
    {
        $data = [
            'name' => $this->input('name'),
            'callback_url' => $this->input('callback_url'),
            'method' => $this->input('method', 'post'),
            'content_type' => $this->input('content_type', 'application/json'),
            'max_attempts' => $this->input('max_attempts', 1),
            'secret' => $this->input('secret'),
            'events' => $this->input('events'),
            'active' => $this->input('active', false),
        ];

        if ($workspace = WorkspaceManager::current()) {
            $data['workspace_id'] = $workspace->id;
        }

        return Webhook::create($data);
    }
}
