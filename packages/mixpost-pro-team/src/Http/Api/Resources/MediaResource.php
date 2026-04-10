<?php

namespace Inovector\Mixpost\Http\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'type' => $this->type(),
            'url' => $this->getUrl(),
            'thumb_url' => $this->isImageGif() ? $this->getUrl() : $this->getThumbUrl(),
            'is_video' => $this->isVideo(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
