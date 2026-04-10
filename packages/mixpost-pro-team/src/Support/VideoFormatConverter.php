<?php

namespace Inovector\Mixpost\Support;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Inovector\Mixpost\Util;

class VideoFormatConverter
{
    public static function toMp4(string $inputPath, string $outputPath): bool
    {
        if (! Util::isFFmpegInstalled()) {
            return false;
        }

        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => Util::config('ffmpeg_path'),
            'ffprobe.binaries' => Util::config('ffprobe_path'),
        ]);

        $video = $ffmpeg->open($inputPath);

        $format = new X264('aac');
        $format->setAdditionalParameters(['-movflags', '+faststart']);

        $video->save($format, $outputPath);

        return true;
    }
}
