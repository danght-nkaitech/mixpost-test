<?php

namespace Inovector\Mixpost\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\Audience;
use Inovector\Mixpost\Models\FacebookInsight;
use Inovector\Mixpost\Models\HashtagGroup;
use Inovector\Mixpost\Models\ImportedPost;
use Inovector\Mixpost\Models\InstagramInsight;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Models\Metric;
use Inovector\Mixpost\Models\PinterestAnalytic;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\PostingSchedule;
use Inovector\Mixpost\Models\Tag;
use Inovector\Mixpost\Models\Template;
use Inovector\Mixpost\Models\Variable;
use Inovector\Mixpost\Models\Webhook;

class DeleteWorkspaceDataJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $workspaceId,
        private readonly string $workspaceUuid
    ) {}

    public function handle(): void
    {
        Post::withoutWorkspace()->where('workspace_id', $this->workspaceId)->forceDelete();
        PostingSchedule::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        Account::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        Tag::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        HashtagGroup::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        Variable::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        Template::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        ImportedPost::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        FacebookInsight::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        InstagramInsight::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        PinterestAnalytic::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        Metric::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        Audience::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
        Webhook::byWorkspace($this->workspaceId)->delete();

        // Delete Media
        Media::withoutWorkspace()
            ->where('workspace_id', $this->workspaceId)
            ->select('disk')
            ->groupBy('disk')
            ->cursor()
            ->each(function ($item) {
                try {
                    Storage::disk($item->disk)->deleteDirectory($this->workspaceUuid);
                } catch (\Exception $exception) {

                }
            });

        Media::withoutWorkspace()->where('workspace_id', $this->workspaceId)->delete();
    }

    public function uniqueId(): string
    {
        return $this->workspaceId;
    }
}
