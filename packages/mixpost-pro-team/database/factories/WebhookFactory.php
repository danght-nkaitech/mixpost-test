<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Enums\WebhookDeliveryStatus;
use Inovector\Mixpost\Models\Webhook;
use Inovector\Mixpost\Models\Workspace;
use Inovector\Mixpost\WebhookManager as MixpostWebhook;

class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'workspace_id' => Workspace::factory(),
            'name' => $this->faker->name,
            'callback_url' => $this->faker->url,
            'method' => $this->faker->randomElement(['post', 'get']),
            'content_type' => $this->faker->randomElement(['application/json', 'application/x-www-form-urlencoded']),
            'max_attempts' => $this->faker->numberBetween(0, 3),
            'last_delivery_status' => $this->faker->randomElement([WebhookDeliveryStatus::SUCCESS, WebhookDeliveryStatus::ERROR]),
            'secret' => $this->faker->password,
            'active' => $this->faker->boolean,
            'events' => MixpostWebhook::getWorkspaceSelectionOptionKeys(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
