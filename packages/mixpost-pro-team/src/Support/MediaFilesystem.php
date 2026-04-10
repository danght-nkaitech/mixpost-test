<?php

namespace Inovector\Mixpost\Support;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RuntimeException;

class MediaFilesystem
{
    /**
     * Copy file from a Laravel disk to local filesystem (streaming).
     */
    public static function copyFromDisk(string $sourceDisk, string $sourceFilepath, string $destinationFilePath): bool|int
    {
        $stream = self::getStream($sourceDisk, $sourceFilepath);

        try {
            $result = File::put($destinationFilePath, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $result;
    }

    /**
     * Copy file from local filesystem to a Laravel disk (streaming).
     */
    public static function copyToDisk(string $destinationDisk, string $destinationFilePath, string $sourceFilePath): bool
    {
        if (! File::exists($sourceFilePath)) {
            throw new RuntimeException("Source file does not exist: $sourceFilePath");
        }

        $stream = fopen($sourceFilePath, 'rb');

        try {
            return Storage::disk($destinationDisk)->writeStream(
                $destinationFilePath,
                $stream,
                ['visibility' => 'public']
            );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Copy file between Laravel disks.
     * Uses server-side copy for same cloud disk (S3), streaming for others.
     */
    public static function copyBetweenDisks(
        string $sourceDisk,
        string $sourceFilepath,
        string $destinationDisk,
        string $destinationFilePath
    ): bool {
        // Same cloud disk: use server-side copy (no data transfer through app)
        if ($sourceDisk === $destinationDisk && self::isCloudDisk($sourceDisk)) {
            $result = self::cloudCopy($sourceDisk, $sourceFilepath, $destinationFilePath);

            if ($result) {
                return true;
            }
            // Fall through to streaming if cloud copy fails
        }

        // Different disks or local: stream through app
        $stream = Storage::disk($sourceDisk)->readStream($sourceFilepath);

        try {
            return Storage::disk($destinationDisk)->writeStream(
                $destinationFilePath,
                $stream,
                ['visibility' => 'public']
            );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public static function getStream(string $disk, string $filepath)
    {
        return Storage::disk($disk)->readStream($filepath);
    }

    public static function isCloudDisk(string $disk): bool
    {
        $filesystem = Storage::disk($disk);

        return ! ($filesystem->getAdapter() instanceof LocalFilesystemAdapter);
    }

    /**
     * Perform server-side copy on cloud storage (S3/compatible).
     * Data stays within the cloud provider.
     */
    public static function cloudCopy(string $disk, string $sourcePath, string $destinationPath): bool
    {
        $filesystem = Storage::disk($disk);

        try {
            /** @phpstan-ignore-next-line */
            $client = $filesystem->getClient();
            $bucket = $filesystem->getConfig()['bucket'];

            $client->copyObject([
                'Bucket' => $bucket,
                'CopySource' => $bucket.'/'.ltrim($sourcePath, '/'),
                'Key' => $destinationPath,
                'ACL' => 'public-read',
                'MetadataDirective' => 'COPY',
            ]);

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
