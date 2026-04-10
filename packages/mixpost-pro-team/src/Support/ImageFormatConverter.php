<?php

namespace Inovector\Mixpost\Support;

use Inovector\Mixpost\Concerns\UsesImageManager;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;

class ImageFormatConverter
{
    use UsesImageManager;

    public function toJpeg(string $inputPath, string $outputPath, int $quality = 90): void
    {
        $image = $this->imageManager($this->resolveDriver($inputPath))->read($inputPath);
        $encoded = $image->toJpeg(quality: $quality);

        file_put_contents($outputPath, $encoded->__toString());
    }

    private function resolveDriver(string $inputPath): ?VipsDriver
    {
        if (! $this->isHeic($inputPath)) {
            return null;
        }

        return new VipsDriver;
    }

    private function isHeic(string $inputPath): bool
    {
        $mimeType = mime_content_type($inputPath);

        return in_array($mimeType, ['image/heic', 'image/heif']);
    }
}
