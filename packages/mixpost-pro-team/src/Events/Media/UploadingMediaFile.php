<?php

namespace Inovector\Mixpost\Events\Media;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Workspace;
use Inovector\Mixpost\Support\MediaSource;

class UploadingMediaFile
{
    use Dispatchable, SerializesModels;

    public ?Workspace $workspace;

    public ?MediaSource $source = null;

    public function __construct(?MediaSource $source = null)
    {
        $this->workspace = WorkspaceManager::current();
        $this->source = $source;
    }
}
