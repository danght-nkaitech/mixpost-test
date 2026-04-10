<?php

namespace Inovector\MixpostEnterprise\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Inovector\MixpostEnterprise\Enums\PlanType;
use Inovector\MixpostEnterprise\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->domainName,
            'type' => PlanType::PAID,
            'monthly_amount' => rand(8, 28),
            'monthly_platform_plan_id' => Str::random(6),
            'yearly_amount' => rand(100, 120),
            'yearly_platform_plan_id' => Str::random(6),
            'enabled' => true,
            'sort_order' => 0,
            'limits' => [],
        ];
    }

    public static function limits($scheduledPosts = 3, $numberOfBrandsSocialAccounts = 1, $numberOfSocialAccounts = 3, $workspaceMembers = 3, $workspaceStorage = 10): array
    {
        return [
            [
                'code' => 'ScheduledPosts',
                'form' => [
                    [
                        'name' => 'count',
                        'value' => $scheduledPosts,
                    ],
                ],
            ],
            [
                'code' => 'NumberOfBrandsSocialAccounts',
                'form' => [
                    [
                        'name' => 'count',
                        'value' => $numberOfBrandsSocialAccounts,
                    ],
                ],
            ],
            [
                'code' => 'NumberOfSocialAccounts',
                'form' => [
                    [
                        'name' => 'count',
                        'value' => $numberOfSocialAccounts,
                    ],
                ],
            ],
            [
                'code' => 'WorkspaceMembers',
                'form' => [
                    [
                        'name' => 'count',
                        'value' => $workspaceMembers,
                    ],
                ],
            ],
            [
                'code' => 'WorkspaceStorage',
                'form' => [
                    [
                        'name' => 'size',
                        'value' => $workspaceStorage,
                    ],
                ],
            ],
        ];
    }
}
