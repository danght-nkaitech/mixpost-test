<?php

namespace Inovector\Mixpost\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Abstracts\Image;
use Inovector\Mixpost\Concerns\UsesMimeType;
use Inovector\Mixpost\Contracts\MediaConversion;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Util;
use League\Flysystem\Local\LocalFilesystemAdapter;

class MediaUploader
{
    use UsesMimeType;

    protected MediaSource $source;

    protected string $disk;

    protected string $path = '';

    protected ?array $data = null;

    protected int $width;

    protected int $height;

    protected array $conversions = [];

    public function __construct(MediaSource $source)
    {
        $this->source = $source;

        $this->disk(Util::config('disk'));

        $this->width = Image::LARGE_WIDTH;
        $this->height = Image::LARGE_HEIGHT;
    }

    /**
     * Create uploader from an uploaded file (HTTP request).
     */
    public static function fromFile(UploadedFile $file): static
    {
        return new static(MediaSource::fromUploadedFile($file));
    }

    /**
     * Create uploader from a local filesystem path.
     */
    public static function fromLocalPath(string $path, ?string $filename = null): static
    {
        return new static(MediaSource::fromLocalPath($path, $filename));
    }

    /**
     * Create uploader from a file on a Laravel disk.
     */
    public static function fromDisk(string $disk, string $path, ?string $filename = null): static
    {
        return new static(MediaSource::fromDisk($disk, $path, $filename));
    }

    /**
     * Create uploader from a MediaSource instance.
     */
    public static function fromSource(MediaSource $source): static
    {
        return new static($source);
    }

    /**
     * Set the source (for advanced use cases).
     */
    public function setSource(MediaSource $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Set file from UploadedFile (backward compatible).
     *
     * @deprecated Use fromFile() or setSource() instead.
     */
    public function setFile(UploadedFile $file): static
    {
        $this->source = MediaSource::fromUploadedFile($file);

        return $this;
    }

    public function disk(string $name): static
    {
        $this->disk = $name;

        return $this;
    }

    public function path(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function data(array $array): static
    {
        $this->data = ! empty($array) ? $array : null;

        return $this;
    }

    public function width(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function conversions(array $array): static
    {
        $this->conversions = $array;

        return $this;
    }

    public function upload(): array
    {
        $mimeType = $this->source->getMimeType();
        $filesystem = $this->filesystem();

        // Determine upload path
        if ($this->isImage($mimeType) && ! $this->isGifImage($mimeType)) {
            $filePath = $this->uploadImage();
        } elseif ($this->isVideo($mimeType) && $mimeType !== 'video/mp4') {
            $filePath = $this->convertAndStoreVideo();
        } else {
            $filePath = $this->source->storeAs($this->disk, $this->path);
        }

        if (! $filePath) {
            throw new \Exception("The file was not uploaded. Check your $this->disk driver configuration.");
        }

        $size = $filesystem->size($filePath);
        $conversions = $this->performConversions($filePath);
        $totalSize = $size + collect($conversions)->sum('size');

        return [
            'name' => pathinfo($this->source->getFilename(), PATHINFO_FILENAME),
            'mime_type' => $this->source->getMimeType(),
            'size' => $size,
            'size_total' => $totalSize,
            'disk' => $this->disk,
            'is_local_driver' => $filesystem->getAdapter() instanceof LocalFilesystemAdapter,
            'path' => $filePath,
            'url' => $filesystem->url($filePath),
            'conversions' => $conversions,
            'data' => $this->data ?: null,
        ];
    }

    public function uploadAndInsert(): Media
    {
        $data = Arr::only($this->upload(), ['name', 'mime_type', 'size', 'size_total', 'disk', 'path', 'conversions', 'data']);

        if (! (isset($this->data['adobe_express_doc_id'])
                &&
                $media = Media::whereJsonContains('data->adobe_express_doc_id', $this->data['adobe_express_doc_id'])->first())) {
            return Media::create($data);
        }

        $media->deleteFiles();
        $media->update($data);

        return $media->refresh();
    }

    protected function performConversions(string $filepath): array
    {
        if (empty($this->conversions)) {
            return [];
        }

        return collect($this->conversions)->map(function ($conversion) use ($filepath) {
            if (! $conversion instanceof MediaConversion) {
                throw new \Exception('The conversion must be an instance of MediaConversion');
            }

            $perform = $conversion->filepath($filepath)->fromDisk($this->disk)->perform();

            if (! $perform) {
                return null;
            }

            return $perform->get();
        })->filter()->values()->toArray();
    }

    protected function uploadImage(): string
    {
        $mimeType = $this->source->getMimeType();
        $isJpeg = in_array($mimeType, ['image/jpeg', 'image/jpg']);
        $isHeic = in_array($mimeType, ['image/heic', 'image/heif']);

        $localPath = $this->source->getLocalPath();
        $preConvertTempDir = null;

        if (! $isJpeg) {
            $this->source->filename(pathinfo($this->source->getFilename(), PATHINFO_FILENAME).'.jpg');
            $this->source->mimeType('image/jpeg');
        }

        // Pre-convert HEIC/HEIF since the GD driver cannot decode them
        if ($isHeic) {
            $preConvertTempDir = TemporaryDirectory::make();
            $preConvertedPath = $preConvertTempDir->path('pre_converted.jpg');
            (new ImageFormatConverter)->toJpeg($localPath, $preConvertedPath);
            $localPath = $preConvertedPath;
        }

        $image = ImageResizer::make($localPath);

        $destinationPath = rtrim($this->path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->source->generateHashedFilename();

        $image->disk($this->disk)
            ->path($destinationPath)
            ->resize(Image::LARGE_WIDTH, null, $isJpeg ? null : 'jpeg');

        $this->source->cleanup();
        $preConvertTempDir?->delete();

        return $image->getDestinationFilePath();
    }

    protected function convertAndStoreVideo(): bool|string
    {
        if (! Util::isFFmpegInstalled()) {
            return $this->source->storeAs($this->disk, $this->path);
        }

        $tempDir = TemporaryDirectory::make();
        $outputPath = $tempDir->path(bin2hex(random_bytes(8)).'.mp4');

        try {
            VideoFormatConverter::toMp4($this->source->getLocalPath(), $outputPath);

            $this->source->cleanup();

            $originalFilename = $this->source->getFilename();
            $this->source = MediaSource::fromLocalPath($outputPath);
            $this->source->filename(pathinfo($originalFilename, PATHINFO_FILENAME).'.mp4');
            $this->source->mimeType('video/mp4');

            return $this->source->storeAs($this->disk, $this->path);
        } finally {
            $tempDir->delete();
        }
    }

    protected function filesystem(): Filesystem
    {
        return Storage::disk($this->disk);
    }
}
