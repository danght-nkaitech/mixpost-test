<?php

namespace Inovector\Mixpost\Actions\Webhook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Enums\WebhookDeliveryStatus;
use Inovector\Mixpost\Models\Webhook;
use Inovector\Mixpost\Models\WebhookDelivery;

class TriggerWebhook
{
    public function __invoke(Webhook $webhook, WebhookEvent $event): Model|WebhookDelivery
    {
        $data = [
            'event' => $event->name(),
            'data' => $event->payload(),
        ];

        try {
            $headers = [
                'Content-Type' => $webhook->content_type,
                'X-Request-Source' => Config::get('app.name'),
            ];

            if ($webhook->secret) {
                $headers['X-Signature'] = hash_hmac('sha256', json_encode($data), $webhook->secret);
            }

            $response = Http::withHeaders($headers)->{$webhook->method->value}($webhook->callback_url, $data);

            $status = match ($response->status()) {
                200, 201, 202 => WebhookDeliveryStatus::SUCCESS,
                default => WebhookDeliveryStatus::ERROR,
            };

            $webhook->updateLastDeliveryStatus($status);

            return $webhook->deliveries()->create([
                'event' => $event->name(),
                'status' => $status,
                'http_status' => $response->status(),
                'payload' => $data,
                'response' => $response->json() ?: $response->body(),
                'created_at' => Carbon::now()->utc(),
            ]);
        } catch (\Exception $e) {
            $status = WebhookDeliveryStatus::ERROR;

            $webhook->updateLastDeliveryStatus($status);

            return $webhook->deliveries()->create([
                'event' => $event->name(),
                'status' => $status,
                'payload' => $data,
                'response' => ['message' => $e->getMessage()],
                'created_at' => Carbon::now()->utc(),
            ]);
        }
    }
}
