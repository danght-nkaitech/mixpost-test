<?php

namespace Inovector\MixpostEnterprise;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Inovector\Mixpost\Schedule as ScheduleCore;
use Inovector\MixpostEnterprise\Models\UsageRecord;
use Inovector\MixpostEnterprise\Models\Workspace;

class Schedule extends ScheduleCore
{
    public static function register($schedule, ?Builder $query = null, ?Closure $customCommands = null): void
    {
        $query = $query ?? Workspace::query()
            ->unlocked()
            ->with('subscriptions');

        parent::register($schedule, $query);

        $schedule->command('model:prune', [
            '--model' => [UsageRecord::class],
        ])->monthly();

        $schedule->command('mixpost-enterprise:users:delete-unverified')->daily();
    }
}
