<?php

namespace Inovector\Mixpost\Listeners\Account;

use Illuminate\Contracts\Queue\ShouldQueue;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Events\Account\AccountUnauthorized as AccountUnauthorizedEvent;
use Inovector\Mixpost\Notifications\AccountUnauthorized as AccountUnauthorizedNotification;

class SendAccountUnauthorizedNotification implements QueueWorkspaceAware, ShouldQueue
{
    public function handle(AccountUnauthorizedEvent $event): void
    {
        $workspace = $event->account->workspace;

        if (! $workspace) {
            return;
        }

        $workspace->users()->wherePivot('role', WorkspaceUserRole::ADMIN)->each(function ($user) use ($event) {
            $user->notify(new AccountUnauthorizedNotification($event->account));
        });
    }
}
