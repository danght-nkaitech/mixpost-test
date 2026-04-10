<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\Mixpost\Concerns\ResourceHasParameters;

class MediaResource extends JsonResource
{
    use ResourceHasParameters;

    public static $wrap = null;

    public function fields()
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'type' => $this->type(),
            'url' => $this->getUrl(),
            'thumb_url' => $this->isImageGif() ? $this->getUrl() : $this->getThumbUrl(),
            'video_custom_thumb_url' => $this->additionalFields['video_custom_thumb_url'] ?? null, // This key is used to resolve the custom video thumbnail URL
            'is_video' => $this->isVideo(),
            'source_url' => $this->source_url ?? null,
            'credit_url' => $this->credit_url ?? null,
            'author' => $this->author ?? null,
            'source' => $this->source ?? null,
            'alt_text' => $this->alt_text ?? null,
            'download_data' => $this->download_data ?? null,
            'adobe_express_doc_id' => $this->adobe_express_doc_id ?? null,
        ];
    }
}
