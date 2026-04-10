<?php

namespace Inovector\Mixpost;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Inovector\Mixpost\Jobs\WorkspaceArtisanJob;
use Inovector\Mixpost\Models\WebhookDelivery;
use Inovector\Mixpost\Models\Workspace;
use Inovector\Mixpost\Support\MediaFilesystem;

class Schedule
{
    public static function register($schedule, ?Builder $query = null, ?Closure $customCommands = null): void
    {
        $schedule->command('model:prune', [
            '--model' => [WebhookDelivery::class],
        ])->monthly();

        $schedule->command('mixpost:prune-temporary-directory')->hourly();

        if (MediaFilesystem::isCloudDisk(Util::config('disk'))) {
            $schedule->command('mixpost:cleanup-multipart-uploads')->daily();
        }

        $query = $query ?? Workspace::query()->select(['id', 'name']);

        $query
            ->each(function (Workspace $workspace) use ($schedule, $customCommands): void {
                if (! $workspace->valid()) {
                    return;
                }

                $schedule
                    ->job(new WorkspaceArtisanJob($workspace, 'mixpost:run-scheduled-posts'))
                    ->name("$workspace->name - mixpost:run-scheduled-posts")
                    ->everyMinute();

                $schedule
                    ->job(new WorkspaceArtisanJob($workspace, 'mixpost:import-account-data'))
                    ->name("$workspace->name - mixpost:import-account-data")
                    ->everyTwoHours();

                $schedule
                    ->job(new WorkspaceArtisanJob($workspace, 'mixpost:import-account-audience'))
                    ->name("$workspace->name - mixpost:import-account-audience")
                    ->everyThreeHours();

                $schedule
                    ->job(new WorkspaceArtisanJob($workspace, 'mixpost:process-metrics'))
                    ->name("$workspace->name - mixpost:process-metrics")
                    ->everyThreeHours();

                $schedule
                    ->job(new WorkspaceArtisanJob($workspace, 'mixpost:check-and-refresh-account-token'))
                    ->name("$workspace->name - mixpost:check-and-refresh-account-token")
                    ->everyTenMinutes();

                $schedule
                    ->job(new WorkspaceArtisanJob($workspace, 'mixpost:prune-trashed-posts'))
                    ->name("$workspace->name - mixpost:prune-trashed-posts")
                    ->daily();

                if ($customCommands) {
                    $customCommands($workspace);
                }
            });
    }
}
