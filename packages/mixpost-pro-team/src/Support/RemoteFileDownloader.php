<?php

namespace Inovector\Mixpost\Support;

use Exception;
use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Concerns\UsesFileConfig;
use Inovector\Mixpost\Data\RemoteFileDownloadData;
use Inovector\Mixpost\Enums\FileSizeUnit;
use Inovector\Mixpost\Exceptions\RemoteFileDownloadException;
use Inovector\Mixpost\Mixpost;

class RemoteFileDownloader
{
    use UsesFileConfig;

    protected string $url;

    protected int $timeout = 30;

    protected int $connectTimeout = 10;

    protected ?array $metadata = null;

    protected ?TemporaryDirectory $temporaryDirectory = null;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public static function make(string $url): static
    {
        return new static($url);
    }

    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function connectTimeout(int $seconds): static
    {
        $this->connectTimeout = $seconds;

        return $this;
    }

    public function temporaryDirectory(TemporaryDirectory $temporaryDirectory): static
    {
        $this->temporaryDirectory = $temporaryDirectory;

        return $this;
    }

    /**
     * Get remote file metadata using HEAD request.
     *
     * @return array{content_type: ?string, content_length: ?int, accessible: bool}
     */
    public function getMetadata(): array
    {
        if ($this->metadata !== null) {
            return $this->metadata;
        }

        $this->metadata = $this->getMetadataViaHead();

        if (! $this->metadata['accessible']) {
            $this->metadata = $this->getMetadataViaRangedGet();
        }

        return $this->metadata;
    }

    protected function getMetadataViaHead(): array
    {
        try {
            $response = Http::timeout($this->connectTimeout)->head($this->url);

            if (! $response->successful()) {
                return $this->inaccessibleMetadata();
            }

            return $this->parseMetadataFromHeaders($response);
        } catch (Exception) {
            return $this->inaccessibleMetadata();
        }
    }

    /**
     * Fallback for services that reject HEAD requests (e.g., S3 presigned URLs).
     * Uses a ranged GET to fetch only 1 byte while still retrieving headers.
     */
    protected function getMetadataViaRangedGet(): array
    {
        try {
            $response = Http::timeout($this->connectTimeout)
                ->withHeaders(['Range' => 'bytes=0-0'])
                ->get($this->url);

            if (! $response->successful()) {
                return $this->inaccessibleMetadata();
            }

            $contentType = $response->header('Content-Type');
            $contentLength = null;

            // Extract total size from Content-Range header (e.g., "bytes 0-0/12345")
            $contentRange = $response->header('Content-Range');

            if ($contentRange && preg_match('/\/(\d+)$/', $contentRange, $matches)) {
                $contentLength = (int) $matches[1];
            }

            return [
                'content_type' => $contentType ? $this->extractMimeType($contentType) : null,
                'content_length' => $contentLength,
                'accessible' => true,
            ];
        } catch (Exception) {
            return $this->inaccessibleMetadata();
        }
    }

    protected function parseMetadataFromHeaders($response): array
    {
        $contentType = $response->header('Content-Type');
        $contentLength = $response->header('Content-Length');

        return [
            'content_type' => $contentType ? $this->extractMimeType($contentType) : null,
            'content_length' => $contentLength !== null ? (int) $contentLength : null,
            'accessible' => true,
        ];
    }

    protected function inaccessibleMetadata(): array
    {
        return [
            'content_type' => null,
            'content_length' => null,
            'accessible' => false,
        ];
    }

    public function validate(): array
    {
        if (! filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new RemoteFileDownloadException(__('validation.url', ['attribute' => 'url']));
        }

        $metadata = $this->getMetadata();

        if (! $metadata['accessible']) {
            throw new RemoteFileDownloadException(__('mixpost::rules.remote_file.not_accessible'));
        }

        if (! $metadata['content_type']) {
            throw new RemoteFileDownloadException(__('mixpost::rules.remote_file.undetermined_file_type'));
        }

        if (! in_array($metadata['content_type'], $this->allowedMimeTypes(), true)) {
            throw new RemoteFileDownloadException(__('mixpost::rules.mime_type_not_allowed', ['type' => $metadata['content_type']]));
        }

        $maxSize = $this->maxSizeForMimeType($metadata['content_type'], FileSizeUnit::BYTES);

        if ($metadata['content_length'] !== null && $maxSize > 0 && $metadata['content_length'] > $maxSize) {
            $fileType = File::isImage($metadata['content_type']) ? 'image' : 'video';
            $maxSizeMb = $this->maxSizeForMimeType($metadata['content_type'], FileSizeUnit::MB);

            throw new RemoteFileDownloadException(__('mixpost::rules.file_max_size', ['type' => $fileType, 'max' => $maxSizeMb]));
        }

        return $metadata;
    }

    public function download(): RemoteFileDownloadData
    {
        $filename = $this->resolveFilename();
        $ownsDirectory = $this->temporaryDirectory === null;
        $tempDir = $this->temporaryDirectory ?? TemporaryDirectory::make();
        $tempFilePath = $tempDir->path($filename);

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->sink($tempFilePath)
                ->get($this->url);

            if (! $response->successful()) {
                throw new RemoteFileDownloadException(__('mixpost::rules.remote_file.download_failed'));
            }

            return new RemoteFileDownloadData(
                temporaryDirectory: $tempDir,
                filepath: $tempFilePath,
                filename: $filename,
            );
        } catch (RemoteFileDownloadException $e) {
            if ($ownsDirectory) {
                $tempDir->delete();
            }

            throw $e;
        } catch (Exception $e) {
            if ($ownsDirectory) {
                $tempDir->delete();
            }

            Mixpost::reportException($e);

            throw new RemoteFileDownloadException(__('mixpost::rules.remote_file.download_failed').': '.$e->getMessage());
        }
    }

    /**
     * @throws RemoteFileDownloadException
     */
    public function validateAndDownload(): RemoteFileDownloadData
    {
        $this->validate();

        return $this->download();
    }

    protected function resolveExtension(): string
    {
        // Try to get extension from URL
        $urlPath = parse_url($this->url, PHP_URL_PATH);

        if ($urlPath) {
            $extension = pathinfo($urlPath, PATHINFO_EXTENSION);

            if ($extension && strlen($extension) <= 5) {
                return $extension;
            }
        }

        // Fall back to mime type mapping
        $metadata = $this->getMetadata();

        if ($mimeType = $metadata['content_type']) {
            return File::mimeToExtension($mimeType) ?? 'bin';
        }

        return 'bin';
    }

    protected function resolveBase(): string
    {
        $urlPath = parse_url($this->url, PHP_URL_PATH);

        if ($urlPath) {
            return pathinfo($urlPath, PATHINFO_FILENAME);
        }

        return 'remote_download';
    }

    protected function resolveFilename(): string
    {
        return $this->resolveBase().'.'.$this->resolveExtension();
    }

    protected function extractMimeType(string $contentType): string
    {
        $parts = explode(';', $contentType);

        return trim($parts[0]);
    }
}
