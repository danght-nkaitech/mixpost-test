<?php

namespace Inovector\MixpostEnterprise\Events\Subscription;

use Illuminate\Foundation\Events\Dispatchable;
use Inovector\MixpostEnterprise\Models\Workspace;

class SubscriptionDeleted
{
    use Dispatchable;

    public Workspace $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }
}
