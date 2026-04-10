<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Inovector\Mixpost\Models\Tag;
use Inovector\Mixpost\Models\Workspace;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'workspace_id' => Workspace::factory(),
            'name' => $this->faker->domainName(),
            'hex_color' => Str::after($this->faker->hexColor(), '#'),
        ];
    }
}
