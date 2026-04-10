<?php

namespace Inovector\Mixpost\Commands;

use Illuminate\Console\Command;
use Inovector\Mixpost\Services\Bluesky\Crypt\PrivateKey;

class GenerateBlueskyPrivateKey extends Command
{
    public $signature = 'mixpost:generate-bluesky-private-key';

    public $description = 'Generate new private key for Bluesky OAuth';

    public function handle(): int
    {
        $private = PrivateKey::create()->privateB64();

        $this->info($private);

        return self::SUCCESS;
    }
}
