<?php

namespace Inovector\Mixpost\Http\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status(),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
            'versions' => PostVersionResource::collection($this->whenLoaded('versions')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'user' => (new UserResource($this->whenLoaded('user')))
                ->only(['name']),
            'scheduled_at' => $this->scheduled_at?->toDateTimeString(),
            'published_at' => $this->published_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
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
