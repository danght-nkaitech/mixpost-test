<?php

namespace Inovector\MixpostEnterprise\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => 'default',
            'platform_subscription_id' => Str::random(10),
            'platform_plan_id' => Str::random(10),
            'status' => SubscriptionStatus::ACTIVE,
            'platform_data' => [],
            'quantity' => 1,
            'trial_ends_at' => null,
            'paused_from' => null,
            'ends_at' => null,
        ];
    }
}
