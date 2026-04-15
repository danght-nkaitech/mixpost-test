<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('queue:prune-batches')->daily();

if (Schema::hasTable('mixpost_workspaces')) {
    \Inovector\MixpostEnterprise\Schedule::register(Schedule::getFacadeRoot());
}
