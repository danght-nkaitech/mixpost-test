<?php

namespace Inovector\MixpostEnterprise\Jobs\Workspace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\Models\WorkspaceService;

class DeleteWorkspaceDataJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $workspaceId,
        private readonly string $workspaceUuid
    ) {}

    public function handle(): void
    {
        WorkspaceService::withoutWorkspace()
            ->byWorkspace($this->workspaceId)
            ->get()
            ->each(function (WorkspaceService $workspaceService) {
                $this->workspace()->execute(fn () => $workspaceService->delete());
            });
    }

    private function workspace(): Workspace
    {
        $model = new Workspace;

        $model->setRawAttributes([
            'id' => $this->workspaceId,
            'uuid' => $this->workspaceUuid,
        ]);

        return $model;
    }

    public function uniqueId(): string
    {
        return $this->workspaceId;
    }
}
