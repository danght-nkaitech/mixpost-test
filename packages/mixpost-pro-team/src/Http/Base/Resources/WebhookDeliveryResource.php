<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\Mixpost\Concerns\ResourceHasParameters;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Util;

class WebhookDeliveryResource extends JsonResource
{
    use ResourceHasParameters;

    public static $wrap = null;

    public function fields(): array
    {
        return [
            'id' => $this->uuid,
            'event' => $this->event,
            'status' => $this->status?->name,
            'http_status' => $this->http_status,
            'payload' => $this->payload ? ['event' => $this->payload['event'] ?? null, 'data' => $this->payload['data'] ?? null] : null,  // MySql json sort the keys, so we need to sort the keys to match http post query
            'response' => $this->response,
            'resend_at' => $this->resend_at?->tz(Settings::get('timezone'))->translatedFormat('M j, '.Util::timeFormat()),
            'resent_manually' => $this->resent_manually,
            'created_at' => $this->created_at->tz(Settings::get('timezone'))->translatedFormat('M j, '.Util::timeFormat()),
        ];
    }
}
