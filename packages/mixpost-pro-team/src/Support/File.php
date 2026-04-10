<?php

namespace Inovector\Mixpost\Support;

use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class File
{
    public static function type(string $mimeType): string
    {
        if (self::isImage($mimeType)) {
            return 'image';
        }

        if (self::isVideo($mimeType)) {
            return 'video';
        }

        return 'file';
    }

    public static function isImage(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    public static function isGif(string $mimeType): bool
    {
        return Str::after($mimeType, '/') === 'gif';
    }

    public static function isVideo(string $mimeType): bool
    {
        return Str::before($mimeType, '/') === 'video';
    }

    public static function mimeToExtension(string $mimeType): ?string
    {
        $extensions = MimeTypes::getDefault()->getExtensions($mimeType);

        return $extensions[0] ?? null;
    }
}
