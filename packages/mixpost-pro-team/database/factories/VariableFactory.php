<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\Variable;
use Inovector\Mixpost\Models\Workspace;

class VariableFactory extends Factory
{
    protected $model = Variable::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'workspace_id' => Workspace::factory(),
            'name' => $this->faker->domainName,
            'value' => $this->faker->paragraph,
        ];
    }
}
