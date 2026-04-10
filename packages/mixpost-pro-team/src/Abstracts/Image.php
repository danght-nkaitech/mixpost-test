<?php

/**
 * This class is responsible for handling image.
 *
 * TODO: Move size constants in `mixpost.php` config file on next major release
 */

namespace Inovector\Mixpost\Abstracts;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Inovector\Mixpost\Concerns\Makeable;
use Inovector\Mixpost\Models\Media;

abstract class Image
{
    use Makeable;

    const LARGE_WIDTH = 1080;

    const LARGE_HEIGHT = 1080;

    const MEDIUM_WIDTH = 430;

    const MEDIUM_HEIGHT = 430;

    const THUMBNAIL_WIDTH = 200;

    const THUMBNAIL_HEIGHT = 200;

    public function __construct(protected readonly UploadedFile|Media|string $file) {}

    protected function getFileData(): string
    {
        return match (true) {
            $this->file instanceof UploadedFile => $this->file->get(),
            $this->file instanceof Media => $this->file->contents(),
            default => $this->file, // When the file is a string, it's the path or contents of the file
        };
    }

    protected function getFileName(): string
    {
        return match (true) {
            $this->file instanceof UploadedFile => $this->file->hashName(),
            $this->file instanceof Media => $this->file->name,
            default => File::basename($this->file) // Works only when the file is a string path
        };
    }
}
