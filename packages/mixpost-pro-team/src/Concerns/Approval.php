<?php

namespace Inovector\Mixpost\Concerns;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Facades\WorkspaceManager;

trait Approval
{
    public function userCanApprove(): bool
    {
        return Auth::user()->canApprove(WorkspaceManager::current());
    }

    public function determineSchedulePostStatus(): PostStatus
    {
        return ! $this->userCanApprove() ? PostStatus::NEEDS_APPROVAL : PostStatus::SCHEDULED;
    }
}
