<?php

namespace Inovector\MixpostEnterprise\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\Admin;
use Inovector\Mixpost\Models\User;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
        ];
    }
}
