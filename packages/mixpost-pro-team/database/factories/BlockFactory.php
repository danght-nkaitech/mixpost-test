<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Enums\ResourceStatus;
use Inovector\Mixpost\Models\Block;

class BlockFactory extends Factory
{
    protected $model = Block::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'module' => 'Editor',
            'content' => [
                'body' => '<p>'.$this->faker->paragraph.'</p>',
            ],
            'status' => ResourceStatus::ENABLED,
        ];
    }
}
