<?php

namespace Inovector\Mixpost\Events\Account;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\SocialProvider;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Workspace;

class AddingAccount
{
    use Dispatchable, SerializesModels;

    public ?Workspace $workspace;

    public SocialProvider $provider;

    public function __construct(SocialProvider $provider)
    {
        $this->workspace = WorkspaceManager::current();
        $this->provider = $provider;
    }
}
