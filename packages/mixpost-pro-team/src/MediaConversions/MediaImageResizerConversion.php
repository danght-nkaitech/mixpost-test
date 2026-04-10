<?php

namespace Inovector\Mixpost\MediaConversions;

use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Abstracts\MediaConversion;
use Inovector\Mixpost\Support\ImageResizer;
use Inovector\Mixpost\Support\MediaConversionData;
use Inovector\Mixpost\Support\TemporaryFile;
use League\Flysystem\Local\LocalFilesystemAdapter;

class MediaImageResizerConversion extends MediaConversion
{
    protected ?float $width;

    protected ?float $height = null;

    public function getEngineName(): string
    {
        return 'ImageResizer';
    }

    public function canPerform(): bool
    {
        return $this->isImage() && ! $this->isGifImage();
    }

    public function getPath(): string
    {
        return $this->getFilePathWithSuffix();
    }

    public function width(?float $value = null): static
    {
        $this->width = $value;

        return $this;
    }

    public function height(?float $value = null): static
    {
        $this->height = $value;

        return $this;
    }

    public function handle(): ?MediaConversionData
    {
        $imageSource = $this->resolveImageSource();

        $imagePath = $imageSource['path'];
        $temporaryFile = $imageSource['temporary_file'];

        ImageResizer::make($imagePath)
            ->disk($this->getToDisk())
            ->path($this->getPath())
            ->resize($this->width, $this->height);

        $temporaryFile?->directory()->delete();

        return MediaConversionData::conversion($this);
    }

    /**
     * Resolve image source path.
     * Uses direct path for local disk.
     * Downloads to temp file for cloud storage.
     */
    private function resolveImageSource(): array
    {
        $disk = $this->getFromDisk();
        $filepath = $this->getFilepath();
        $filesystem = Storage::disk($disk);

        // Local disk: use file directly, no need to copy
        if ($filesystem->getAdapter() instanceof LocalFilesystemAdapter) {
            return [
                'path' => $filesystem->path($filepath),
                'temporary_file' => null,
            ];
        }

        // Cloud storage: download to temp file (image libraries can't read from URLs)
        $temporaryFile = TemporaryFile::make()->fromDisk(
            sourceDisk: $disk,
            sourceFilepath: $filepath
        );

        return [
            'path' => $temporaryFile->filepath(),
            'temporary_file' => $temporaryFile,
        ];
    }
}
