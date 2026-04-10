<?php

namespace Inovector\Mixpost\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class MediaSource
{
    private ?UploadedFile $uploadedFile = null;

    private ?string $localPath = null;

    private ?string $diskName = null;

    private ?string $diskPath = null;

    private ?string $customFilename = null;

    private ?string $customMimeType = null;

    private ?TemporaryDirectory $temporaryDirectory = null;

    private function __construct() {}

    public static function fromUploadedFile(UploadedFile $file): self
    {
        $instance = new self;
        $instance->uploadedFile = $file;

        return $instance;
    }

    public static function fromLocalPath(string $path, ?string $filename = null): self
    {
        if (! FileFacade::exists($path)) {
            throw new RuntimeException("File does not exist: $path");
        }

        $instance = new self;
        $instance->localPath = $path;
        $instance->customFilename = $filename;

        return $instance;
    }

    public static function fromDisk(string $disk, string $path, ?string $filename = null): self
    {
        if (! Storage::disk($disk)->exists($path)) {
            throw new RuntimeException("File does not exist on disk '$disk': $path");
        }

        $instance = new self;
        $instance->diskName = $disk;
        $instance->diskPath = $path;
        $instance->customFilename = $filename;

        return $instance;
    }

    public function filename(string $filename): self
    {
        $this->customFilename = $filename;

        return $this;
    }

    public function mimeType(string $mimeType): self
    {
        $this->customMimeType = $mimeType;

        return $this;
    }

    public function getFilename(): string
    {
        if ($this->customFilename) {
            return $this->customFilename;
        }

        if ($this->uploadedFile) {
            return $this->uploadedFile->getClientOriginalName();
        }

        if ($this->localPath) {
            return FileFacade::basename($this->localPath);
        }

        if ($this->diskPath) {
            return basename($this->diskPath);
        }

        return 'unknown';
    }

    public function getMimeType(): string
    {
        if ($this->customMimeType) {
            return $this->customMimeType;
        }

        if ($this->uploadedFile) {
            return $this->uploadedFile->getMimeType();
        }

        if ($this->localPath) {
            return FileFacade::mimeType($this->localPath) ?: 'application/octet-stream';
        }

        if ($this->diskName && $this->diskPath) {
            return Storage::disk($this->diskName)->mimeType($this->diskPath) ?: 'application/octet-stream';
        }

        return 'application/octet-stream';
    }

    public function getContents(): string
    {
        if ($this->uploadedFile) {
            return $this->uploadedFile->get();
        }

        if ($this->localPath) {
            return FileFacade::get($this->localPath);
        }

        if ($this->diskName && $this->diskPath) {
            return Storage::disk($this->diskName)->get($this->diskPath);
        }

        throw new RuntimeException('No file source configured');
    }

    public function getExtension(): string
    {
        $filename = $this->getFilename();

        return pathinfo($filename, PATHINFO_EXTENSION) ?: 'bin';
    }

    /**
     * Get a local file path for this source.
     * For disk sources, this downloads to a temporary directory.
     */
    public function getLocalPath(): string
    {
        if ($this->uploadedFile) {
            return $this->uploadedFile->getRealPath();
        }

        if ($this->localPath) {
            return $this->localPath;
        }

        if ($this->diskName && $this->diskPath) {
            return $this->downloadToTemp();
        }

        throw new RuntimeException('No file source configured');
    }

    /**
     * Store the file to the target disk.
     *
     * @return bool|string The file path on success, false on failure
     */
    public function storeAs(string $disk, string $path): bool|string
    {
        $filesystem = Storage::disk($disk);

        if ($this->uploadedFile) {
            return $filesystem->putFile($path, $this->uploadedFile, 'public');
        }

        if ($this->localPath) {
            $filename = $this->generateHashedFilename();
            $fullPath = rtrim($path, '/').'/'.$filename;

            // Use multipart upload for large files to cloud storage
            if ($this->shouldUseMultipartUpload($disk)) {
                return ServerSideMultipartUpload::make($this->localPath)
                    ->disk($disk)
                    ->destinationPath($fullPath)
                    ->mimeType($this->getMimeType())
                    ->upload();
            }

            $stream = fopen($this->localPath, 'rb');

            try {
                $result = $filesystem->writeStream($fullPath, $stream, ['visibility' => 'public']);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            return $result ? $fullPath : false;
        }

        if ($this->diskName && $this->diskPath) {
            $filename = $this->generateHashedFilename();
            $fullPath = rtrim($path, '/').'/'.$filename;

            // Use S3 server-side copy if same disk and non-local (S3/cloud)
            if ($this->diskName === $disk && MediaFilesystem::isCloudDisk($disk)) {
                return MediaFilesystem::cloudCopy($disk, $this->diskPath, $fullPath)
                    ? $fullPath
                    : false;
            }

            // Fall back to streaming for cross-disk or local transfers
            $stream = Storage::disk($this->diskName)->readStream($this->diskPath);
            $result = $filesystem->writeStream($fullPath, $stream, ['visibility' => 'public']);

            if (is_resource($stream)) {
                fclose($stream);
            }

            return $result ? $fullPath : false;
        }

        throw new RuntimeException('No file source configured');
    }

    public function generateHashedFilename(): string
    {
        $extension = $this->getExtension();

        return bin2hex(random_bytes(20)).'.'.$extension;
    }

    /**
     * Determine if multipart upload should be used for this file.
     * Uses multipart for files > 100MB going to cloud storage.
     */
    private function shouldUseMultipartUpload(string $disk): bool
    {
        if (! MediaFilesystem::isCloudDisk($disk)) {
            return false;
        }

        if (! $this->localPath) {
            return false;
        }

        $fileSize = FileFacade::size($this->localPath);
        $threshold = 100 * 1024 * 1024; // 100MB

        return $fileSize > $threshold;
    }

    private function downloadToTemp(): string
    {
        $this->temporaryDirectory = TemporaryDirectory::make();
        $tempPath = $this->temporaryDirectory->path($this->getFilename());

        MediaFilesystem::copyFromDisk(
            sourceDisk: $this->diskName,
            sourceFilepath: $this->diskPath,
            destinationFilePath: $tempPath
        );

        return $tempPath;
    }

    public function cleanup(): void
    {
        $this->temporaryDirectory?->delete();
        $this->temporaryDirectory = null;
    }

    public function __destruct()
    {
        $this->cleanup();
    }
}
