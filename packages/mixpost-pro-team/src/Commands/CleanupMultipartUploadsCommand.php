<?php

namespace Inovector\Mixpost\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Support\MediaFilesystem;
use Inovector\Mixpost\Util;

class CleanupMultipartUploadsCommand extends Command
{
    protected $signature = 'mixpost:cleanup-multipart-uploads
                            {--hours=24 : Abort uploads older than this many hours}
                            {--disk= : Disk to clean up (defaults to configured disk)}
                            {--dry-run : List uploads without aborting}';

    protected $description = 'Abort incomplete S3 multipart uploads older than specified hours';

    public function handle(): int
    {
        $disk = $this->option('disk') ?: Util::config('disk', 'public');
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');

        if (! MediaFilesystem::isCloudDisk($disk)) {
            $this->info("Disk '{$disk}' is not a cloud disk. Nothing to clean up.");

            return self::SUCCESS;
        }

        $this->info($dryRun ? 'Listing incomplete multipart uploads...' : 'Cleaning up incomplete multipart uploads...');

        $aborted = $this->cleanupIncompleteUploads($disk, $hours, $dryRun);

        if ($aborted === 0) {
            $this->info('No incomplete multipart uploads found.');
        } else {
            $action = $dryRun ? 'Found' : 'Aborted';
            $this->info("{$action} {$aborted} incomplete multipart upload(s).");
        }

        return self::SUCCESS;
    }

    protected function cleanupIncompleteUploads(string $disk, int $hours, bool $dryRun): int
    {
        $filesystem = Storage::disk($disk);

        try {
            /** @phpstan-ignore-next-line */
            $client = $filesystem->getClient();
            $bucket = $filesystem->getConfig()['bucket'];
        } catch (Exception $e) {
            $this->error("Failed to get S3 client: {$e->getMessage()}");

            return 0;
        }

        $cutoff = Carbon::now()->subHours($hours);
        $abortedCount = 0;
        $keyMarker = null;
        $uploadIdMarker = null;

        do {
            $params = ['Bucket' => $bucket];

            if ($keyMarker !== null) {
                $params['KeyMarker'] = $keyMarker;
                $params['UploadIdMarker'] = $uploadIdMarker;
            }

            try {
                $result = $client->listMultipartUploads($params);
            } catch (Exception $e) {
                $this->error("Failed to list multipart uploads: {$e->getMessage()}");

                return $abortedCount;
            }

            $uploads = $result['Uploads'] ?? [];

            foreach ($uploads as $upload) {
                $initiated = Carbon::parse($upload['Initiated']);

                if ($initiated->lt($cutoff)) {
                    $key = $upload['Key'];
                    $uploadId = $upload['UploadId'];
                    $age = $initiated->diffForHumans();

                    if ($dryRun) {
                        $this->line("  [DRY-RUN] Would abort: {$key} (started {$age})");
                        $abortedCount++;
                    } else {
                        try {
                            $client->abortMultipartUpload([
                                'Bucket' => $bucket,
                                'Key' => $key,
                                'UploadId' => $uploadId,
                            ]);

                            $this->line("  Aborted: {$key} (started {$age})");
                            $abortedCount++;
                        } catch (Exception $e) {
                            $this->warn("  Failed to abort {$key}: {$e->getMessage()}");
                        }
                    }
                }
            }

            $keyMarker = $result['NextKeyMarker'] ?? null;
            $uploadIdMarker = $result['NextUploadIdMarker'] ?? null;
            $isTruncated = $result['IsTruncated'] ?? false;

        } while ($isTruncated && $keyMarker !== null);

        return $abortedCount;
    }
}
