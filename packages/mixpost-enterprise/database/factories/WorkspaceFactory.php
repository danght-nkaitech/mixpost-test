<?php

namespace Inovector\MixpostEnterprise\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Inovector\Mixpost\Models\User;
use Inovector\MixpostEnterprise\Enums\WorkspaceAccessStatus;
use Inovector\MixpostEnterprise\Models\Workspace;

class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->domainName(),
            'hex_color' => Str::after($this->faker->hexColor(), '#'),
            'owner_id' => User::factory(),
            'access_status' => WorkspaceAccessStatus::SUBSCRIPTION,
            'generic_subscription_plan_id' => null,
            'generic_subscription_free' => false,
            'generic_trial_ends_at' => null, // Carbon::now()->addDays(7),
            'limits' => null,
        ];
    }
}
