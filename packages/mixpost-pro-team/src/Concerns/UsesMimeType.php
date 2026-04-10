<?php

namespace Inovector\Mixpost\Concerns;

use Illuminate\Support\Str;

trait UsesMimeType
{
    public function isImage(string $mimeType): bool
    {
        return Str::before($mimeType, '/') === 'image';
    }

    public function isGifImage(string $mimeType): bool
    {
        return $this->isImage($mimeType) && Str::after($mimeType, '/') === 'gif';
    }

    public function isVideo(string $mimeType): bool
    {
        return Str::before($mimeType, '/') === 'video';
    }
}
