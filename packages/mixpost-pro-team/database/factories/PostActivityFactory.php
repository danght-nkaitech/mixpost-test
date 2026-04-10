<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Enums\PostActivityType;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\PostActivity;
use Inovector\Mixpost\Models\User;

class PostActivityFactory extends Factory
{
    protected $model = PostActivity::class;

    public function definition()
    {
        $types = PostActivityType::cases();

        $type = Arr::random($types);

        return [
            'uuid' => $this->faker->uuid,
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'type' => $type->value,
            'data' => [],
            'text' => $type === PostActivityType::COMMENT ? $this->faker->sentence : null,
        ];
    }

    public function fromUser(User $user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    public function forPost(Post $post): Factory
    {
        return $this->state(function (array $attributes) use ($post) {
            return [
                'post_id' => $post->id,
            ];
        });
    }

    public function comment(?string $text = ''): Factory
    {
        return $this->state(function (array $attributes) use ($text) {
            return [
                'type' => PostActivityType::COMMENT,
                'text' => $text ?: $this->faker->sentence,
            ];
        });
    }

    public function replyTo(int|PostActivity $parent_id = 0): Factory
    {
        return $this->state(function (array $attributes) use ($parent_id) {
            return [
                'parent_id' => $parent_id instanceof PostActivity ? $parent_id->id : $parent_id,
            ];
        });
    }
}
