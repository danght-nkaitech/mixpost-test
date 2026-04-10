<?php

namespace Inovector\Mixpost\Events\Account;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Workspace;

class StoringAccountEntities
{
    use Dispatchable, SerializesModels;

    public ?Workspace $workspace;

    public string $provider;

    public array $items;

    public function __construct(string $provider, array $items)
    {
        $this->workspace = WorkspaceManager::current();
        $this->provider = $provider;
        $this->items = $items;
    }
}
