<?php

namespace Inovector\MixpostEnterprise\Events\Workspace;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Models\Workspace;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;

class WorkspaceCreated implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public Workspace $workspace;

    public bool $fromAdmin;

    public function __construct(Workspace $workspace, bool $fromAdmin = false)
    {
        $this->workspace = $workspace;
        $this->fromAdmin = $fromAdmin;
    }

    public static function name(): string
    {
        return 'workspace.created';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost-enterprise::webhook.event.workspace.created');
    }

    public function payload(): array
    {
        $this->workspace->load('owner');

        return [
            'workspace' => (new WorkspaceResource($this->workspace))->resolve(),
            'from_admin' => $this->fromAdmin,
        ];
    }
}
