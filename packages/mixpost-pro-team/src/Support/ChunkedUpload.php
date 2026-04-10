<?php

namespace Inovector\Mixpost\Support;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesFileConfig;
use Inovector\Mixpost\Enums\FileSizeUnit;
use Inovector\Mixpost\Exceptions\ChunkedUploadSessionNotFound;
use Inovector\Mixpost\Util;
use JsonException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RuntimeException;

class ChunkedUpload
{
    use UsesFileConfig;

    private const MIN_CHUNK_SIZE_MB = 10;

    private const MAX_CHUNK_SIZE_MB = 64;

    private const MAX_CHUNKS = 1000;

    protected string $disk;

    protected ?string $tempDirPrefix = null;

    public function __construct()
    {
        $this->disk = Util::config('disk', 'public');
    }

    public function tempDirPrefix(string $prefix): self
    {
        $this->tempDirPrefix = $prefix;

        return $this;
    }

    public function getChunkSize(): int
    {
        return $this->chunkedUploadChunkSize(FileSizeUnit::BYTES);
    }

    public function getThreshold(): int
    {
        return $this->chunkedUploadThreshold(FileSizeUnit::BYTES);
    }

    public function shouldChunk(int $fileSize): bool
    {
        return $fileSize > $this->getThreshold();
    }

    /**
     * @throws JsonException
     */
    public function initiate(string $filename, string $mimeType, int $totalSize, array $metadata = []): array
    {
        $uploadUuid = (string) Str::uuid();
        $chunkPlan = $this->buildChunkPlan($totalSize);

        $isS3 = ! $this->isLocalDisk();

        $sessionData = [
            'filename' => $filename,
            'mime_type' => $mimeType,
            'total_size' => $totalSize,
            'total_chunks' => $chunkPlan['total_chunks'],
            'chunk_size' => $chunkPlan['chunk_size'],
            'disk' => $this->disk,
            'is_s3' => $isS3,
            'metadata' => $metadata,
            's3_upload_id' => null,
            's3_key' => null,
            's3_parts' => [],
        ];

        if ($isS3) {
            $initData = $this->initiateS3Multipart($uploadUuid, $filename, $mimeType);
            $sessionData['s3_upload_id'] = $initData['upload_id'];
            $sessionData['s3_key'] = $initData['key'];
        }

        $this->createTempDirectory($uploadUuid);
        $this->saveSessionData($uploadUuid, $sessionData);

        return [
            'upload_uuid' => $uploadUuid,
            'chunk_size' => $chunkPlan['chunk_size'],
            'total_chunks' => $chunkPlan['total_chunks'],
        ];
    }

    /**
     * @throws ChunkedUploadSessionNotFound
     * @throws JsonException
     */
    public function uploadChunk(string $uploadUuid, int $chunkIndex, UploadedFile $chunk): array
    {
        $session = $this->getSessionData($uploadUuid);

        if ($session['is_s3']) {
            return $this->uploadChunkS3($uploadUuid, $chunkIndex, $chunk, $session);
        }

        return $this->uploadChunkLocal($uploadUuid, $chunkIndex, $chunk);
    }

    /**
     * @throws ChunkedUploadSessionNotFound
     * @throws JsonException
     */
    public function complete(string $uploadUuid): MediaSource
    {
        $session = $this->getSessionData($uploadUuid);

        if ($session['is_s3']) {
            return $this->completeS3($uploadUuid, $session);
        }

        return $this->completeLocal($uploadUuid, $session);
    }

    /**
     * @throws ChunkedUploadSessionNotFound
     * @throws JsonException
     */
    public function abort(string $uploadUuid): void
    {
        $session = $this->getSessionData($uploadUuid);

        if ($session['is_s3'] && $session['s3_upload_id']) {
            $this->abortS3($session['s3_upload_id'], $session['s3_key']);
        }

        $this->cleanup($uploadUuid);
    }

    public function cleanup(string $uploadUuid): void
    {
        $tempDir = $this->getTempDirectory($uploadUuid);

        // Clean up S3 temp file if applicable
        try {
            $session = $this->getSessionData($uploadUuid);

            if ($session['is_s3'] && ! empty($session['s3_key'])) {
                $this->filesystem()->delete($session['s3_key']);
            }
        } catch (ChunkedUploadSessionNotFound) {
            // Session already cleaned up, ignore
        }

        if (is_dir($tempDir)) {
            $this->deleteDirectory($tempDir);
        }
    }

    protected function buildChunkPlan(int $fileSize): array
    {
        if ($fileSize <= 0) {
            throw new RuntimeException('File size must be greater than 0');
        }

        $configuredChunkSize = $this->getChunkSize();

        if ($fileSize <= $configuredChunkSize) {
            return [
                'chunk_size' => $fileSize,
                'total_chunks' => 1,
            ];
        }

        $minChunkSize = FileSize::mbToBytes(self::MIN_CHUNK_SIZE_MB);
        $maxChunkSize = FileSize::mbToBytes(self::MAX_CHUNK_SIZE_MB);

        $chunkSize = max($minChunkSize, min($maxChunkSize, $configuredChunkSize));

        $minNeeded = (int) ceil($fileSize / self::MAX_CHUNKS);
        if ($minNeeded > $chunkSize) {
            $chunkSize = min($minNeeded, $maxChunkSize);
        }

        $totalChunks = (int) ceil($fileSize / $chunkSize);

        if ($totalChunks > self::MAX_CHUNKS) {
            throw new RuntimeException('File too large: would exceed maximum chunk count');
        }

        return [
            'chunk_size' => $chunkSize,
            'total_chunks' => $totalChunks,
        ];
    }

    protected function isLocalDisk(): bool
    {
        return $this->filesystem()->getAdapter() instanceof LocalFilesystemAdapter;
    }

    protected function filesystem(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    protected function getTempDirectory(string $uploadUuid): string
    {
        $path = TemporaryDirectory::getParentTemporaryDirectoryPath()
            .DIRECTORY_SEPARATOR
            .'chunked';

        if ($this->tempDirPrefix) {
            $path .= DIRECTORY_SEPARATOR.$this->tempDirPrefix;
        }

        return $path.DIRECTORY_SEPARATOR.$uploadUuid;
    }

    protected function createTempDirectory(string $uploadUuid): string
    {
        $path = $this->getTempDirectory($uploadUuid);

        if (! is_dir($path)) {
            if (! mkdir($path, 0755, true) && ! is_dir($path)) {
                throw new RuntimeException("Failed to create temp directory: {$path}");
            }
        }

        return $path;
    }

    protected function saveSessionData(string $uploadUuid, array $data): void
    {
        $tempDir = $this->getTempDirectory($uploadUuid);

        if (! is_dir($tempDir)) {
            $this->createTempDirectory($uploadUuid);
        }

        $sessionPath = $tempDir.DIRECTORY_SEPARATOR.'session.json';
        $result = file_put_contents($sessionPath, json_encode($data, JSON_THROW_ON_ERROR));

        if ($result === false) {
            throw new RuntimeException("Failed to save session data: {$sessionPath}");
        }
    }

    /**
     * @throws ChunkedUploadSessionNotFound
     * @throws JsonException
     */
    public function getSessionData(string $uploadUuid): array
    {
        $tempDir = $this->getTempDirectory($uploadUuid);
        $sessionPath = $tempDir.DIRECTORY_SEPARATOR.'session.json';

        if (! file_exists($sessionPath)) {
            throw new ChunkedUploadSessionNotFound($uploadUuid);
        }

        return json_decode(file_get_contents($sessionPath), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function uploadChunkLocal(string $uploadUuid, int $chunkIndex, UploadedFile $chunk): array
    {
        $tempDir = $this->getTempDirectory($uploadUuid);

        if (! is_dir($tempDir)) {
            throw new RuntimeException('Upload session not found');
        }

        $chunkPath = $tempDir.DIRECTORY_SEPARATOR.'chunk_'.$chunkIndex;
        $chunk->move($tempDir, 'chunk_'.$chunkIndex);

        return [
            'chunk_index' => $chunkIndex,
            'size' => filesize($chunkPath),
        ];
    }

    protected function completeLocal(string $uploadUuid, array $session): MediaSource
    {
        $tempDir = $this->getTempDirectory($uploadUuid);

        $assembledPath = $tempDir.DIRECTORY_SEPARATOR.'assembled';
        $output = fopen($assembledPath, 'wb');

        if ($output === false) {
            throw new RuntimeException('Failed to create assembled file');
        }

        try {
            for ($i = 0; $i < $session['total_chunks']; $i++) {
                $chunkPath = $tempDir.DIRECTORY_SEPARATOR.'chunk_'.$i;

                if (! file_exists($chunkPath)) {
                    throw new RuntimeException("Missing chunk: {$i}");
                }

                $chunkHandle = fopen($chunkPath, 'rb');

                if ($chunkHandle === false) {
                    throw new RuntimeException("Failed to open chunk: {$i}");
                }

                stream_copy_to_stream($chunkHandle, $output);
                fclose($chunkHandle);
            }
        } finally {
            fclose($output);
        }

        return MediaSource::fromLocalPath($assembledPath, $session['filename'])
            ->mimeType($session['mime_type']);
    }

    protected function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    protected function initiateS3Multipart(string $uploadUuid, string $filename, string $mimeType): array
    {
        $filesystem = $this->filesystem();
        $client = $filesystem->getClient();
        $bucket = $filesystem->getConfig()['bucket'];

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $key = 'chunked-uploads/'.$uploadUuid.'/'.$uploadUuid.'.'.$extension;

        $result = $client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $key,
            'ContentType' => $mimeType,
        ]);

        return [
            'upload_id' => $result['UploadId'],
            'key' => $key,
        ];
    }

    protected function uploadChunkS3(string $uploadUuid, int $chunkIndex, UploadedFile $chunk, array $session): array
    {
        $filesystem = $this->filesystem();
        $client = $filesystem->getClient();
        $bucket = $filesystem->getConfig()['bucket'];

        $result = $client->uploadPart([
            'Bucket' => $bucket,
            'Key' => $session['s3_key'],
            'UploadId' => $session['s3_upload_id'],
            'PartNumber' => $chunkIndex + 1,
            'Body' => fopen($chunk->getRealPath(), 'rb'),
        ]);

        $session['s3_parts'][] = [
            'part_number' => $chunkIndex + 1,
            'etag' => $result['ETag'],
        ];

        $this->saveSessionData($uploadUuid, $session);

        return [
            'chunk_index' => $chunkIndex,
            'part_number' => $chunkIndex + 1,
        ];
    }

    protected function completeS3(string $uploadUuid, array $session): MediaSource
    {
        $filesystem = $this->filesystem();
        $client = $filesystem->getClient();
        $bucket = $filesystem->getConfig()['bucket'];

        $parts = $session['s3_parts'];
        usort($parts, fn ($a, $b) => $a['part_number'] <=> $b['part_number']);

        $multipartParts = array_map(fn ($part) => [
            'PartNumber' => $part['part_number'],
            'ETag' => $part['etag'],
        ], $parts);

        $client->completeMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $session['s3_key'],
            'UploadId' => $session['s3_upload_id'],
            'MultipartUpload' => [
                'Parts' => $multipartParts,
            ],
        ]);

        return MediaSource::fromDisk($this->disk, $session['s3_key'], $session['filename'])
            ->mimeType($session['mime_type']);
    }

    protected function abortS3(string $s3UploadId, string $s3Key): void
    {
        try {
            $filesystem = $this->filesystem();
            $client = $filesystem->getClient();
            $bucket = $filesystem->getConfig()['bucket'];

            $client->abortMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $s3Key,
                'UploadId' => $s3UploadId,
            ]);
        } catch (Exception $e) {
            // Silently fail - upload may have already been aborted or completed
        }
    }
}
