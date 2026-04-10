<?php

namespace Inovector\Mixpost\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Abstracts\Image;
use Inovector\Mixpost\Concerns\UsesMediaPath;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Enums\RemoteMediaDownloadStatus;
use Inovector\Mixpost\MediaConversions\MediaImageResizerConversion;
use Inovector\Mixpost\MediaConversions\MediaVideoThumbConversion;
use Inovector\Mixpost\Support\MediaUploader;
use Inovector\Mixpost\Support\RemoteFileDownloader;
use Inovector\Mixpost\Support\RemoteMediaDownloadTracker;

class DownloadRemoteMediaJob implements QueueWorkspaceAware, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use UsesMediaPath;

    public int $timeout = 600; // 10 minutes

    public int $tries = 1;

    public function __construct(
        public string $downloadId,
        public string $url,
        public array $fileData = []
    ) {}

    public function handle(): void
    {
        RemoteMediaDownloadTracker::updateStatus($this->downloadId, RemoteMediaDownloadStatus::DOWNLOADING);

        $file = null;

        try {
            $file = RemoteFileDownloader::make($this->url)
                ->timeout($this->timeout - 60) // Leave some buffer for processing
                ->validateAndDownload();

            RemoteMediaDownloadTracker::updateStatus($this->downloadId, RemoteMediaDownloadStatus::PROCESSING);

            $media = MediaUploader::fromLocalPath($file->filepath, $file->filename)
                ->path($this->mediaWorkspacePathWithDateSubpath())
                ->conversions([
                    MediaImageResizerConversion::name('thumb')->width(Image::MEDIUM_WIDTH)->height(Image::MEDIUM_HEIGHT),
                    MediaVideoThumbConversion::name('thumb')->atSecond(5),
                ])
                ->data($this->fileData)
                ->uploadAndInsert();

            RemoteMediaDownloadTracker::markCompleted($this->downloadId, $media->id);
        } catch (Exception $e) {
            RemoteMediaDownloadTracker::markFailed($this->downloadId, 'rules.remote_file.download_failed');
        } finally {
            $file?->temporaryDirectory->delete();
        }
    }
}
