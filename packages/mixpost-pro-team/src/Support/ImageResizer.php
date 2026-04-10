<?php

namespace Inovector\Mixpost\Support;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Abstracts\Image;
use Inovector\Mixpost\Concerns\UsesImageManager;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Util;

final class ImageResizer extends Image
{
    use UsesImageManager;

    protected string $disk;

    protected string $path;

    public function __construct(UploadedFile|Media|string $file)
    {
        parent::__construct($file);

        $this->disk(Util::config('disk'));
        $this->path = '';
    }

    public function disk(string $name): ImageResizer
    {
        $this->disk = $name;

        return $this;
    }

    public function path(string $path): ImageResizer
    {
        $this->path = $path;

        return $this;
    }

    public function resize(?int $width = null, ?int $height = null, ?string $encodeFormat = null): bool
    {
        $image = $this->imageManager()->read($this->getFileData())->scaleDown($width, $height);

        if (! $path = $this->getDestinationFilePath()) {
            throw new Exception('The destination path is not set. Possible reason: you are using the contents of the file. Specify the path where the file will be saved.');
        }

        $encoded = $encodeFormat === 'jpeg'
            ? $image->toJpeg(quality: 90)
            : $image->encodeByMediaType(quality: 90);

        return Storage::disk($this->getDisk())->put(
            path: $path,
            contents: $encoded->__toString(),
            options: 'public'
        );
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getDestinationFilePath(): string
    {
        if ($this->file instanceof UploadedFile) {
            return rtrim($this->file->hashName($this->resolvePath()), DIRECTORY_SEPARATOR);
        }

        if ($this->isFilePath($this->resolvePath())) {
            return rtrim($this->resolvePath(), DIRECTORY_SEPARATOR);
        }

        return rtrim($this->resolvePath().$this->getFileName(), DIRECTORY_SEPARATOR);
    }

    private function resolvePath(): string
    {
        if (! $this->path && $this->file instanceof Media) {
            return $this->file->path;
        }

        return $this->path ? rtrim($this->path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR : '';
    }

    private function isFilePath(string $path): bool
    {
        return str_contains($path, '.');
    }
}
