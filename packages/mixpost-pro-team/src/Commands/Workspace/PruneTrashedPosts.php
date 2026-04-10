<?php

namespace Inovector\Mixpost\Commands\Workspace;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Inovector\Mixpost\Models\Post;

class PruneTrashedPosts extends Command
{
    public $signature = 'mixpost:prune-trashed-posts';

    public $description = 'Prune trashed posts that were deleted 30 days ago';

    public function handle(): int
    {
        Post::onlyTrashed()->where('deleted_at', '<=', Carbon::now('UTC')->subDays(30))->forceDelete();

        return self::SUCCESS;
    }
}
