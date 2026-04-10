<?php

namespace Inovector\MixpostEnterprise\Actions\Workspace;

use Inovector\MixpostEnterprise\Models\WorkspaceService;

class UpdateOrCreateService
{
    public function __invoke(string $name, array $configuration, bool $active = false): WorkspaceService
    {
        return WorkspaceService::updateOrCreate(['name' => $name], [
            'configuration' => $configuration,
            'active' => $active,
        ]);
    }
}
