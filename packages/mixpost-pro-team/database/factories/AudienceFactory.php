<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\Audience;
use Inovector\Mixpost\Models\Workspace;

class AudienceFactory extends Factory
{
    protected $model = Audience::class;

    public function definition()
    {
        return [
            'workspace_id' => Workspace::factory(),
            'account_id' => Account::factory(),
            'total' => $this->faker->numberBetween(1, 100000),
            'date' => $this->faker->dateTimeBetween('-90 days'),
        ];
    }
}
