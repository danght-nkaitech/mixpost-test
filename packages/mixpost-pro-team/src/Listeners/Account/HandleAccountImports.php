<?php

namespace Inovector\Mixpost\Listeners\Account;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;
use Inovector\Mixpost\Commands\Workspace\ImportAccountAudience;
use Inovector\Mixpost\Commands\Workspace\ImportAccountData;
use Inovector\Mixpost\Commands\Workspace\ProcessMetrics;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;

class HandleAccountImports implements QueueWorkspaceAware, ShouldQueue
{
    public function handle(object $event): void
    {
        Artisan::call(ImportAccountAudience::class, [
            '--accounts' => $event->account->id,
        ]);

        Artisan::call(ImportAccountData::class, [
            '--accounts' => $event->account->id,
        ]);

        Artisan::call(ProcessMetrics::class, [
            '--accounts' => $event->account->id,
        ]);
    }
}
