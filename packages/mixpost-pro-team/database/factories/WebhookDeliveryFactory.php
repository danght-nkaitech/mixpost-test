<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Enums\WebhookDeliveryStatus;
use Inovector\Mixpost\Models\Webhook;
use Inovector\Mixpost\Models\WebhookDelivery;
use Inovector\Mixpost\WebhookManager as MixpostWebhook;

class WebhookDeliveryFactory extends Factory
{
    protected $model = WebhookDelivery::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'webhook_id' => Webhook::factory(),
            'event' => $this->faker->randomElement(MixpostWebhook::getWorkspaceSelectionOptionKeys()),
            'attempts' => $this->faker->numberBetween(0, 3),
            'status' => $this->faker->randomElement([WebhookDeliveryStatus::SUCCESS, WebhookDeliveryStatus::ERROR]),
            'http_status' => $this->faker->randomElement([200, 201, 419, 404, 500]),
            'resent_manually' => $this->faker->boolean,
            'payload' => [
                'id' => $this->faker->uuid,
                'name' => $this->faker->name,
                'email' => $this->faker->email,
            ],
            'response' => [
                'message' => $this->faker->sentence,
            ],
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
