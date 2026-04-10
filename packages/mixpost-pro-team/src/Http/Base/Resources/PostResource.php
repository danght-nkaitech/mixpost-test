<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Util;

class PostResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id' => $this->uuid,
            'status' => $this->status(),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
            'versions' => PostVersionResource::collection($this->whenLoaded('versions')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'user' => new UserResource($this->whenLoaded('user')),
            'scheduled_at' => [
                'date' => $this->scheduled_at?->tz(Settings::get('timezone'))->toDateString(),
                'time' => $this->scheduled_at?->tz(Settings::get('timezone'))->format('H:i'),
                'human' => $this->scheduled_at?->tz(Settings::get('timezone'))->translatedFormat('D, M j, '.Util::timeFormat()),
            ],
            'published_at' => [
                'human' => $this->published_at?->tz(Settings::get('timezone'))->translatedFormat('D, M j, '.Util::timeFormat()),
            ],
            'trashed' => $this->trashed(),
        ];
    }

    protected function status()
    {
        if ($this->isScheduleProcessing()) {
            return 'publishing';
        }

        return strtolower($this->status->name);
    }
}
