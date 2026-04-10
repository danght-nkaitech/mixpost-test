<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Enums\InstagramInsightType;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\InstagramInsight;

class InstagramInsightFactory extends Factory
{
    protected $model = InstagramInsight::class;

    public function definition()
    {
        return [
            'account_id' => Account::factory(),
            'type' => InstagramInsightType::IMPRESSIONS,
            'value' => $this->faker->numberBetween(),
            'date' => $this->faker->dateTimeBetween('-90 days'),
        ];
    }
}
