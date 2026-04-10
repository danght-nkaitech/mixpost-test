<?php

namespace Inovector\Mixpost\Support;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ServerSideMultipartUpload
{
    private const MIN_PART_SIZE = 5 * 1024 * 1024; // 5MB - S3 minimum

    private const DEFAULT_PART_SIZE = 10 * 1024 * 1024; // 10MB

    protected string $disk;

    protected string $localFilePath;

    protected string $destinationPath;

    protected ?string $mimeType = null;

    protected int $partSize;

    public function __construct(string $localFilePath)
    {
        $this->localFilePath = $localFilePath;
        $this->partSize = self::DEFAULT_PART_SIZE;
    }

    public static function make(string $localFilePath): static
    {
        return new static($localFilePath);
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function destinationPath(string $path): static
    {
        $this->destinationPath = $path;

        return $this;
    }

    public function mimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function partSize(int $bytes): static
    {
        $this->partSize = max(self::MIN_PART_SIZE, $bytes);

        return $this;
    }

    /**
     * Upload the local file to S3 using multipart upload.
     *
     * @return string The destination path on success
     *
     * @throws RuntimeException
     */
    public function upload(): string
    {
        if (! file_exists($this->localFilePath)) {
            throw new RuntimeException("Source file does not exist: {$this->localFilePath}");
        }

        $filesystem = $this->filesystem();

        /** @phpstan-ignore-next-line */
        $client = $filesystem->getClient();
        $bucket = $filesystem->getConfig()['bucket'];

        $mimeType = $this->mimeType ?? mime_content_type($this->localFilePath) ?: 'application/octet-stream';

        // Initiate multipart upload
        $multipartUpload = $client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $this->destinationPath,
            'ContentType' => $mimeType,
            'ACL' => 'public-read',
        ]);

        $uploadId = $multipartUpload['UploadId'];
        $parts = [];

        try {
            $fileHandle = fopen($this->localFilePath, 'rb');

            if ($fileHandle === false) {
                throw new RuntimeException("Failed to open source file: {$this->localFilePath}");
            }

            $partNumber = 1;

            while (! feof($fileHandle)) {
                $chunk = fread($fileHandle, $this->partSize);

                if ($chunk === false || $chunk === '') {
                    break;
                }

                $result = $client->uploadPart([
                    'Bucket' => $bucket,
                    'Key' => $this->destinationPath,
                    'UploadId' => $uploadId,
                    'PartNumber' => $partNumber,
                    'Body' => $chunk,
                ]);

                $parts[] = [
                    'PartNumber' => $partNumber,
                    'ETag' => $result['ETag'],
                ];

                $partNumber++;
            }

            fclose($fileHandle);

            // Complete multipart upload
            $client->completeMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $this->destinationPath,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);

            return $this->destinationPath;
        } catch (Exception $e) {
            // Abort multipart upload on failure
            try {
                $client->abortMultipartUpload([
                    'Bucket' => $bucket,
                    'Key' => $this->destinationPath,
                    'UploadId' => $uploadId,
                ]);
            } catch (Exception) {
                // Ignore abort failures
            }

            throw new RuntimeException('Multipart upload failed: '.$e->getMessage(), 0, $e);
        }
    }

    protected function filesystem(): Filesystem
    {
        return Storage::disk($this->disk);
    }
}
