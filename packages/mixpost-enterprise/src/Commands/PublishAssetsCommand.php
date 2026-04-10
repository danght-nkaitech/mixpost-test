<?php

namespace Inovector\MixpostEnterprise\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    public $signature = 'mixpost-enterprise:publish-assets {--force=}';

    public $description = 'Publish compiled assets to your public folder';

    public function handle(): int
    {
        $force = boolval($this->option('force'));

        if (! $force && File::exists(public_path('vendor/mixpost-enterprise'))) {
            $this->line('Your application already have the Mixpost Enterprise assets');

            if (! $this->confirm('Do you want to rewrite?')) {
                return self::FAILURE;
            }
        }

        File::deleteDirectory(public_path('vendor/mixpost-enterprise'));
        File::copyDirectory(__DIR__.'/../../resources/dist/vendor', public_path('vendor'));
        File::copy(__DIR__.'/../../resources/img/favicon.ico', public_path('vendor/mixpost-enterprise/favicon.ico'));

        $this->info('Assets was published to [public/vendor/mixpost-enterprise]');

        return self::SUCCESS;
    }
}
