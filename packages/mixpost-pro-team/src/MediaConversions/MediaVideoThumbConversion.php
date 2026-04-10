<?php

namespace Inovector\Mixpost\MediaConversions;

use Exception;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Abstracts\Image;
use Inovector\Mixpost\Abstracts\MediaConversion;
use Inovector\Mixpost\Support\ImageResizer;
use Inovector\Mixpost\Support\MediaConversionData;
use Inovector\Mixpost\Support\TemporaryDirectory;
use Inovector\Mixpost\Support\TemporaryFile;
use Inovector\Mixpost\Util;
use League\Flysystem\Local\LocalFilesystemAdapter;

class MediaVideoThumbConversion extends MediaConversion
{
    protected float $atSecond = 0;

    public function getEngineName(): string
    {
        return 'VideoThumb';
    }

    public function canPerform(): bool
    {
        return $this->isVideo();
    }

    public function getPath(): string
    {
        return $this->getFilePathWithSuffix('jpg');
    }

    public function atSecond(float $value = 0): static
    {
        $this->atSecond = $value;

        return $this;
    }

    public function handle(): ?MediaConversionData
    {
        if (! Util::isFFmpegInstalled()) {
            return null;
        }

        $videoSource = $this->resolveVideoSource();
        $videoPath = $videoSource['path'];
        $temporaryFile = $videoSource['temporary_file'];

        // Create temp directory for thumbnail output
        $tempDir = TemporaryDirectory::make();
        $thumbFilepath = $tempDir->path('thumb_'.bin2hex(random_bytes(8)).'.jpg');

        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => Util::config('ffmpeg_path'),
            'ffprobe.binaries' => Util::config('ffprobe_path'),
        ]);

        $video = $ffmpeg->open($videoPath);
        $duration = $ffmpeg->getFFProbe()->format($videoPath)->get('duration');

        // Ensure $seconds is within valid bounds
        $seconds = ($duration > 0 && $this->atSecond > 0) ? min($this->atSecond, floor($duration)) : 0;

        $frame = $video->frame(TimeCode::fromSeconds($seconds));
        $frame->save($thumbFilepath);

        // Sometimes the frame is not saved, so we save it again with the first frame
        // This is a workaround for the issue
        if ($this->atSecond !== 0 && ! File::exists($thumbFilepath)) {
            $frame = $video->frame(TimeCode::fromSeconds(0));
            $frame->save($thumbFilepath);
        }

        // Resize the thumbnail and save it to the destination disk
        ImageResizer::make($thumbFilepath)
            ->disk($this->getToDisk())
            ->path($this->getPath())
            ->resize(Image::MEDIUM_WIDTH, Image::MEDIUM_HEIGHT);

        // Clean up
        $tempDir->delete();
        $temporaryFile?->directory()->delete();

        return MediaConversionData::conversion($this);
    }

    /**
     * Resolve video source path.
     * Uses direct path for local disk.
     * Uses presigned URL for cloud storage (streams only needed portion).
     */
    private function resolveVideoSource(): array
    {
        $disk = $this->getFromDisk();
        $filepath = $this->getFilepath();
        $filesystem = Storage::disk($disk);

        // Local disk: use file directly, no need to copy
        if ($filesystem->getAdapter() instanceof LocalFilesystemAdapter) {
            return [
                'path' => $filesystem->path($filepath),
                'temporary_file' => null,
            ];
        }

        // Cloud storage: use presigned URL (FFmpeg streams only needed portion)
        try {
            return [
                'path' => $filesystem->temporaryUrl($filepath, Carbon::now()->addMinutes(30)),
                'temporary_file' => null,
            ];
        } catch (Exception) {
            // Fall back to full download if presigned URL fails
            $temporaryFile = TemporaryFile::make()->fromDisk(
                sourceDisk: $disk,
                sourceFilepath: $filepath
            );

            return [
                'path' => $temporaryFile->filepath(),
                'temporary_file' => $temporaryFile,
            ];
        }
    }
}
