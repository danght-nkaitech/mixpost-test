<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Inovector\Mixpost\Models\User;
use Inovector\Mixpost\Models\UserToken;

class UserTokenFactory extends Factory
{
    protected $model = UserToken::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word,
            'token' => hash('sha256', Str::random(40)),
            'last_used_at' => null,
            'expires_at' => null,
        ];
    }
}
