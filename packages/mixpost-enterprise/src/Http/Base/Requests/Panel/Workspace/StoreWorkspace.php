<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\MixpostEnterprise\Events\Workspace\WorkspaceCreated;
use Inovector\MixpostEnterprise\Models\Workspace;

class StoreWorkspace extends WorkspaceFormRequest
{
    public function handle(): ?Workspace
    {
        $workspace = Workspace::create($this->requestData());

        if ($this->input('owner_id')) {
            $workspace->attachUser(
                id: $this->input('owner_id'),
                role: WorkspaceUserRole::ADMIN,
                canApprove: true
            );
        }

        WorkspaceCreated::dispatch($workspace, true);

        return $workspace;
    }
}
