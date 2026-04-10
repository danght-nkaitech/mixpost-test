<?php

namespace Inovector\Mixpost\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Support\MediaFilesystem;
use Inovector\Mixpost\Util;

class SetupMultipartUploadsCleanupCommand extends Command
{
    protected $signature = 'mixpost:setup-multipart-cleanup
                            {--days=1 : Days after which incomplete uploads are deleted}
                            {--disk= : Disk to configure (defaults to configured disk)}
                            {--remove : Remove the lifecycle rule instead of adding it}';

    protected $description = 'Configure S3 lifecycle rule to automatically clean up incomplete multipart uploads';

    private const RULE_ID = 'mixpost-cleanup-incomplete-uploads';

    public function handle(): int
    {
        $disk = $this->option('disk') ?: Util::config('disk', 'public');
        $days = (int) $this->option('days');
        $remove = $this->option('remove');

        if (! MediaFilesystem::isCloudDisk($disk)) {
            $this->error("Disk '{$disk}' is not a cloud disk. Lifecycle rules only apply to S3-compatible storage.");

            return self::FAILURE;
        }

        if ($remove) {
            return $this->removeLifecycleRule($disk);
        }

        return $this->addLifecycleRule($disk, $days);
    }

    protected function addLifecycleRule(string $disk, int $days): int
    {
        $filesystem = Storage::disk($disk);

        try {
            /** @phpstan-ignore-next-line */
            $client = $filesystem->getClient();
            $bucket = $filesystem->getConfig()['bucket'];

            // Get existing lifecycle configuration
            $existingRules = $this->getExistingRules($client, $bucket);

            // Remove our rule if it exists (to update it)
            $existingRules = array_filter($existingRules, fn ($rule) => ($rule['ID'] ?? '') !== self::RULE_ID);

            // Add our rule
            $existingRules[] = [
                'ID' => self::RULE_ID,
                'Status' => 'Enabled',
                'Filter' => ['Prefix' => ''],
                'AbortIncompleteMultipartUpload' => [
                    'DaysAfterInitiation' => $days,
                ],
            ];

            $client->putBucketLifecycleConfiguration([
                'Bucket' => $bucket,
                'LifecycleConfiguration' => [
                    'Rules' => array_values($existingRules),
                ],
            ]);

            $this->info("Lifecycle rule configured successfully on bucket '{$bucket}'.");
            $this->info("Incomplete multipart uploads will be automatically deleted after {$days} day(s).");

            return self::SUCCESS;
        } catch (Exception $e) {
            if ($this->isUnsupportedOperation($e)) {
                $this->error('Your storage provider does not support lifecycle rules.');
                $this->line('');
                $this->line('Alternative: Schedule the cleanup command instead:');
                $this->line('  php artisan mixpost:cleanup-multipart-uploads --hours=24');
                $this->line('');
                $this->line('Add to your scheduler (app/Console/Kernel.php):');
                $this->line('  $schedule->command(\'mixpost:cleanup-multipart-uploads\')->daily();');

                return self::FAILURE;
            }

            $this->error("Failed to configure lifecycle rule: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function removeLifecycleRule(string $disk): int
    {
        $filesystem = Storage::disk($disk);

        try {
            /** @phpstan-ignore-next-line */
            $client = $filesystem->getClient();
            $bucket = $filesystem->getConfig()['bucket'];

            $existingRules = $this->getExistingRules($client, $bucket);

            // Remove our rule
            $filteredRules = array_filter($existingRules, fn ($rule) => ($rule['ID'] ?? '') !== self::RULE_ID);

            if (count($filteredRules) === count($existingRules)) {
                $this->info('Lifecycle rule not found. Nothing to remove.');

                return self::SUCCESS;
            }

            if (empty($filteredRules)) {
                $client->deleteBucketLifecycle(['Bucket' => $bucket]);
            } else {
                $client->putBucketLifecycleConfiguration([
                    'Bucket' => $bucket,
                    'LifecycleConfiguration' => [
                        'Rules' => array_values($filteredRules),
                    ],
                ]);
            }

            $this->info("Lifecycle rule removed successfully from bucket '{$bucket}'.");

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Failed to remove lifecycle rule: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function getExistingRules($client, string $bucket): array
    {
        try {
            $result = $client->getBucketLifecycleConfiguration(['Bucket' => $bucket]);

            return $result['Rules'] ?? [];
        } catch (Exception $e) {
            // No lifecycle configuration exists
            if (str_contains($e->getMessage(), 'NoSuchLifecycleConfiguration')) {
                return [];
            }

            throw $e;
        }
    }

    protected function isUnsupportedOperation(Exception $e): bool
    {
        $message = strtolower($e->getMessage());

        $indicators = [
            'not implemented',
            'notimplemented',
            'unsupported',
            'not supported',
            'unknown operation',
            'invalidaction',
            'accessdenied', // Some providers return access denied for unsupported operations
        ];

        foreach ($indicators as $indicator) {
            if (str_contains($message, $indicator)) {
                return true;
            }
        }

        return false;
    }
}
