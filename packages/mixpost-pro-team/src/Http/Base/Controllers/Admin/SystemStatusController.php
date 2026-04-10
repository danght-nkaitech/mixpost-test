<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Composer\InstalledVersions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Broadcast;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Support\HorizonStatus;
use Inovector\Mixpost\Support\TemporaryDirectory;
use Inovector\Mixpost\Util;

class SystemStatusController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Admin/System/Status', [
            'env' => App::environment(),
            'debug' => config('app.debug'),
            'horizonStatus' => resolve(HorizonStatus::class)->get(),
            'hasQueueConnection' => config('queue.connections.mixpost-redis') && ! empty(config('queue.connections.mixpost-redis')),
            'lastScheduledRun' => $this->getLastScheduleRun(),
            'broadcastDriver' => Broadcast::driver(),
            'cacheDriver' => config('cache.default'),
            'basePath' => base_path(),
            'disk' => config('mixpost.disk'),
            'logChannel' => config('mixpost.log_channel') ? config('mixpost.log_channel') : config('logging.default'),
            'userAgent' => $request->userAgent(),
            'uploadMaxFilesize' => ini_get('upload_max_filesize'),
            'postMaxSize' => ini_get('post_max_size'),
            'versions' => [
                'php' => PHP_VERSION,
                'laravel' => App::version(),
                'horizon' => InstalledVersions::getVersion('laravel/horizon'),
                'mysql' => $this->mysqlVersion(),
                'mixpost' => InstalledVersions::getVersion('inovector/mixpost-pro-team'),
                'mixpost_enterprise' => Mixpost::getEnterpriseVersion(),
            ],
            'gdStatus' => extension_loaded('gd') ? __('mixpost::system.backend.installed') : __('mixpost::system.backend.not_installed'),
            'ffmpegStatus' => Util::isFFmpegInstalled() ? __('mixpost::system.backend.installed') : __('mixpost::system.backend.not_installed'),
            'temporaryDirectorySize' => (new TemporaryDirectory)->getSize(),
        ]);
    }

    protected function getLastScheduleRun(): array
    {
        $lastScheduleRun = Cache::get('mixpost-last-schedule-run');

        if (! $lastScheduleRun) {
            return [
                'variant' => 'error',
                'message' => __('mixpost::system.never_started'),
            ];
        }

        $diff = (int) abs(Carbon::now('UTC')->diffInMinutes($lastScheduleRun));

        if ($diff < 10) {
            return [
                'variant' => 'success',
                'message' => __('mixpost::system.ran_time_ago', ['time' => $diff]),
            ];
        }

        return [
            'variant' => 'warning',
            'message' => __('mixpost::system.ran_time_ago', ['time' => $diff]),
        ];
    }

    protected function mysqlVersion(): string
    {
        if (! Util::isMysqlDatabase()) {
            return '';
        }

        $results = DB::select('select version() as version');

        return (string) $results[0]->version;
    }
}
