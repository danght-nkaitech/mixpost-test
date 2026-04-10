<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\HashtagGroup;
use Inovector\Mixpost\Models\Workspace;

class HashtagGroupFactory extends Factory
{
    protected $model = HashtagGroup::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'workspace_id' => Workspace::factory(),
            'name' => $this->faker->domainName,
            'content' => $this->faker->paragraph,
        ];
    }
}
