<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'callback_url' => $this->callback_url,
            'method' => $this->method?->value,
            'content_type' => $this->content_type,
            'max_attempts' => $this->max_attempts,
            'active' => $this->active,
            'events' => $this->events ?: [],
            'last_delivery_status' => $this->last_delivery_status?->name,
        ];
    }
}
