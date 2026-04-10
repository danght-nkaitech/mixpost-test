<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\Mixpost\Util;

class TokenResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_used_at' => $this->last_used_at?->translatedFormat('M j, Y, '.Util::timeFormat()),
            'expires_at' => $this->expires_at?->translatedFormat('M j, Y'),
            'created_at' => $this->created_at->translatedFormat('M j, Y, '.Util::timeFormat()),
        ];
    }
}
