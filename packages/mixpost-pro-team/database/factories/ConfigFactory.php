<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\Config;

class ConfigFactory extends Factory
{
    protected $model = Config::class;

    public function definition()
    {
        return [
            'group' => 'ai',
            'name' => 'provider',
            'payload' => 'openai',
        ];
    }
}
