<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Enums\FacebookInsightType;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\FacebookInsight;
use Inovector\Mixpost\Models\Workspace;

class FacebookInsightFactory extends Factory
{
    protected $model = FacebookInsight::class;

    public function definition()
    {
        return [
            'workspace_id' => Workspace::factory(),
            'account_id' => Account::factory()->state([
                'provider' => 'facebook_page',
            ]),
            'type' => FacebookInsightType::PAGE_POSTS_IMPRESSIONS,
            'value' => $this->faker->randomDigit(),
            'date' => $this->faker->dateTimeBetween('-90 days'),
        ];
    }
}
