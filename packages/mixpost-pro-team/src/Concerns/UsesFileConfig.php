<?php

namespace Inovector\Mixpost\Concerns;

use Inovector\Mixpost\Enums\FileSizeUnit;
use Inovector\Mixpost\Support\FileSize;
use Inovector\Mixpost\Util;

trait UsesFileConfig
{
    public function maxImageSize(FileSizeUnit $unit = FileSizeUnit::MB): int|float
    {
        return $this->convert((int) Util::config('max_file_size.image', 15), $unit);
    }

    public function maxGifSize(FileSizeUnit $unit = FileSizeUnit::MB): int|float
    {
        return $this->convert((int) Util::config('max_file_size.gif', 15), $unit);
    }

    public function maxVideoSize(FileSizeUnit $unit = FileSizeUnit::MB): int|float
    {
        return $this->convert((int) Util::config('max_file_size.video', 200), $unit);
    }

    public function maxSizeForMimeType(string $mimeType, FileSizeUnit $unit = FileSizeUnit::MB): int|float
    {
        if (str_starts_with($mimeType, 'video/')) {
            return $this->maxVideoSize($unit);
        }

        if ($mimeType === 'image/gif') {
            return $this->maxGifSize($unit);
        }

        if (str_starts_with($mimeType, 'image/')) {
            return $this->maxImageSize($unit);
        }

        return 0;
    }

    public function chunkedUploadThreshold(FileSizeUnit $unit = FileSizeUnit::MB): int|float
    {
        return $this->convert((int) Util::config('chunked_upload.threshold', 10), $unit);
    }

    public function chunkedUploadChunkSize(FileSizeUnit $unit = FileSizeUnit::MB): int|float
    {
        return $this->convert((int) Util::config('chunked_upload.chunk_size', 10), $unit);
    }

    public function allowedMimeTypes(): array
    {
        return Util::config('mime_types');
    }

    protected function convert(int $mb, FileSizeUnit $unit): int|float
    {
        return match ($unit) {
            FileSizeUnit::BYTES => FileSize::mbToBytes($mb),
            FileSizeUnit::KB => FileSize::mbToKb($mb),
            FileSizeUnit::MB => $mb,
        };
    }
}
