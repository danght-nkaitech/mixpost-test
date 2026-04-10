<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Inovector\Mixpost\Abstracts\Image;
use Inovector\Mixpost\Concerns\UsesMediaFileDataRules;
use Inovector\Mixpost\Concerns\UsesMediaPath;
use Inovector\Mixpost\Enums\RemoteMediaDownloadStatus;
use Inovector\Mixpost\Events\Media\UploadingMediaFile;
use Inovector\Mixpost\Exceptions\RemoteFileDownloadException;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;
use Inovector\Mixpost\Jobs\DownloadRemoteMediaJob;
use Inovector\Mixpost\MediaConversions\MediaImageResizerConversion;
use Inovector\Mixpost\MediaConversions\MediaVideoThumbConversion;
use Inovector\Mixpost\Rules\RemoteFileRule;
use Inovector\Mixpost\Support\MediaUploader;
use Inovector\Mixpost\Support\RemoteFileDownloader;
use Inovector\Mixpost\Support\RemoteMediaDownloadTracker;
use Inovector\Mixpost\Util;

class MediaRemoteUpload extends FormRequest
{
    use UsesMediaFileDataRules;
    use UsesMediaPath;

    public function rules(): array
    {
        return array_merge([
            'url' => ['required', 'url', 'active_url', new RemoteFileRule],
        ], $this->mediaFileDataRules());
    }

    public function handle(): array
    {
        UploadingMediaFile::dispatch();

        $url = $this->input('url');
        $fileData = $this->extractFileData();

        if ($this->shouldDownloadSynchronously($url)) {
            return $this->downloadSynchronously($url, $fileData);
        }

        return $this->downloadAsynchronously($url, $fileData);
    }

    protected function shouldDownloadSynchronously(string $url): bool
    {
        $thresholdMb = 10;
        $thresholdBytes = $thresholdMb * 1024 * 1024;

        $metadata = RemoteFileDownloader::make($url)->getMetadata();

        if ($metadata['content_length'] === null) {
            return true;
        }

        return $metadata['content_length'] < $thresholdBytes;
    }

    protected function downloadSynchronously(string $url, array $fileData): array
    {
        $file = null;

        try {
            $file = RemoteFileDownloader::make($url)
                ->timeout(Util::getRequestTimeout())
                ->download();

            $media = MediaUploader::fromLocalPath($file->filepath, $file->filename)
                ->path($this->mediaWorkspacePathWithDateSubpath())
                ->conversions([
                    MediaImageResizerConversion::name('thumb')->width(Image::MEDIUM_WIDTH)->height(Image::MEDIUM_HEIGHT),
                    MediaVideoThumbConversion::name('thumb')->atSecond(5),
                ])
                ->data($fileData)
                ->uploadAndInsert();

            return [
                'status' => RemoteMediaDownloadStatus::COMPLETED->value,
                'media' => new MediaResource($media),
            ];
        } catch (RemoteFileDownloadException $e) {
            return [
                'status' => RemoteMediaDownloadStatus::FAILED->value,
                'error' => $e->getMessage(),
            ];
        } finally {
            $file?->temporaryDirectory->delete();
        }
    }

    protected function downloadAsynchronously(string $url, array $fileData): array
    {
        $downloadId = Str::uuid()->toString();

        RemoteMediaDownloadTracker::create($downloadId, $url, WorkspaceManager::current()->id);

        DownloadRemoteMediaJob::dispatch(
            downloadId: $downloadId,
            url: $url,
            fileData: $fileData
        );

        return [
            'download_id' => $downloadId,
            'status' => RemoteMediaDownloadStatus::PENDING->value,
        ];
    }
}
