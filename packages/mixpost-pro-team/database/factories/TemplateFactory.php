<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\Template;
use Inovector\Mixpost\Models\Workspace;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'workspace_id' => Workspace::factory(),
            'name' => $this->faker->domainName(),
            'content' => [],
        ];
    }
}
