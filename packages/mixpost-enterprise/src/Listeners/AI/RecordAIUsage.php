<?php

namespace Inovector\MixpostEnterprise\Listeners\AI;

use Illuminate\Support\Carbon;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Enums\UsageType;
use Inovector\MixpostEnterprise\Models\UsageRecord;

class RecordAIUsage
{
    public function handle(): void
    {
        if (WorkspaceManager::current()->unlimitedAccess()) {
            return;
        }

        UsageRecord::create([
            'type' => UsageType::AI,
            'created_at' => Carbon::now(),
        ]);
    }
}
