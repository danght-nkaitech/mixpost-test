<?php

namespace Inovector\MixpostEnterprise\FeatureLimitResources;

use Illuminate\Support\Carbon;
use Inovector\MixpostEnterprise\Abstracts\FeatureLimitResource;
use Inovector\MixpostEnterprise\Enums\UsageType;
use Inovector\MixpostEnterprise\FeatureLimitFormFields\CountNumber;
use Inovector\MixpostEnterprise\Models\UsageRecord;
use Inovector\MixpostEnterprise\Support\FeatureLimitResponse;

class AICredits extends FeatureLimitResource
{
    public string $name = 'AI Credits';

    public string $description = 'The number of AI credits available for use.';

    public function form(): array
    {
        return [
            CountNumber::make('count')->default(function () {
                return 40;
            }),
        ];
    }

    public function validator(?object $data = null): FeatureLimitResponse
    {
        $value = $this->getValue('count');

        if ($value === null) {
            return $this->makePasses();
        }

        $used = UsageRecord::withoutWorkspace()
            ->byWorkspace($data->workspace)
            ->where('type', UsageType::AI)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        if ($used < (int) $value) {
            return $this->makePasses();
        }

        return $this->makeFails()
            ->withMessages(__('mixpost-enterprise::feature_limit.ai_credits_reached', ['limit' => $value]));
    }
}
