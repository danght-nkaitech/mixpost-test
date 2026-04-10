<?php

namespace Inovector\Mixpost;

use DateTimeInterface;
use DateTimeZone;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Support\TemporaryDirectory;

class Util
{
    use UsesUserModel;

    public static function config(string $key, mixed $default = null)
    {
        return Config::get("mixpost.$key", $default);
    }

    public static function corePath(): string
    {
        return self::config('core_path', 'mixpost');
    }

    public static function appName(): string
    {
        return Config::get('app.name');
    }

    public static function isMixpostRequest(Request $request): bool
    {
        $path = self::corePath();

        return $request->is($path) ||
            $request->is("$path/*");
    }

    public static function isHorizonRequest(): bool
    {
        return request()->route() && request()->route()->getPrefix() === 'horizon/api';
    }

    public static function convertTimeToUTC(string|DateTimeInterface|null $time = null, DateTimeZone|string|null $tz = null): Carbon
    {
        return Carbon::parse($time, $tz ?: Settings::get('timezone'))->utc();
    }

    public static function dateTimeFormat(Carbon $datetime, DateTimeZone|string|null $tz = null): string
    {
        $format = $datetime->year === now($tz)->year ? 'M j, '.self::timeFormat() : 'M j, Y, '.self::timeFormat();

        return $datetime->tz($tz ?: Settings::get('timezone'))->translatedFormat($format);
    }

    public static function timeFormat(): string
    {
        return Settings::get('time_format') == 24 ? 'H:i' : 'h:ia';
    }

    public static function isTimestampString(string $string): bool
    {
        // Regular expression to match the date format: Y-m-dTH:i:s.uZ
        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/';

        return preg_match($pattern, $string) === 1;
    }

    public static function removeHtmlTags($string): string
    {
        if (! $string) {
            return '';
        }

        $text = trim(strip_tags($string));

        return html_entity_decode($text);
    }

    public static function isAdminConsole(Request $request): bool
    {
        return $request->route() && Str::contains($request->route()->getPrefix(), (Util::corePath().'/admin'));
    }

    public static function isPublicDomainUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);

        if (empty($parsedUrl['host'])) {
            return false;
        }

        // Validate URL format
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Check if the host part is an IP address (both IPv4 and IPv6)
        if (filter_var($parsedUrl['host'], FILTER_VALIDATE_IP)) {
            return false;
        }

        if (in_array($parsedUrl['host'], ['localhost', '127.0.0.1', '::1'])) {
            return false;
        }

        return true;
    }

    public static function estimateUploadTimeout(int $size, int $defaultTimeout = 30)
    {
        $uploadSpeed = 2 * 1024 * 1024; // 2MB/s
        $estimatedTimeout = $size / $uploadSpeed;

        return max($estimatedTimeout, $defaultTimeout);
    }

    public static function estimateDelayByFileSize(int $fileSize): array
    {
        $initial = (int) round(max(15, $fileSize / 1000000)); // Set a delay proportional to the file size
        $max = (int) round(min(5 * 60, $fileSize / 500000)); // Set a max delay proportional to the file size

        return [
            'initial' => $initial,
            'max' => $max,
        ];
    }

    public static function closeAndDeleteStreamResource(array $stream): void
    {
        if (is_resource($stream['stream'])) {
            fclose($stream['stream']);
        }

        if (isset($stream['temporaryDirectory']) && $stream['temporaryDirectory'] instanceof TemporaryDirectory) {
            $stream['temporaryDirectory']->delete();
        }
    }

    public static function performHttpRequestWithTimeoutRetries(callable $httpRequestFunction, int $timeout = 30, int $maxAttempts = 3)
    {
        $startTimeout = $timeout;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                return $httpRequestFunction($timeout);
            } catch (ConnectionException $e) {
                $timeout += $startTimeout;
            }
        }

        return null;
    }

    public static function performTaskWithDelay(callable $task, int $initialDelay = 15, int $maxDelay = 60, int $maxAttempts = 10, bool $increaseDelay = true)
    {
        $delay = $initialDelay;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $result = $task();

            if ($result !== null) {
                return $result;
            }

            sleep($delay);

            if ($increaseDelay) {
                // Increase delay for the next iteration, maxing out at maxDelay
                $delay = min($delay * 2, $maxDelay);
                // Add a random jitter to the delay
                $delay += rand(-(int) ($delay * 0.1), (int) ($delay * 0.1));
            }

            $attempt++;
        }

        return null;
    }

    public static function getDatabaseDriver(?string $connection = null): string
    {
        $key = is_null($connection) ? Config::get('database.default') : $connection;

        return strtolower(Config::get('database.connections.'.$key.'.driver'));
    }

    public static function isMysqlDatabase(?string $connection = null): bool
    {
        return self::getDatabaseDriver($connection) === 'mysql';
    }

    public static function isFFmpegInstalled(): bool
    {
        $ffmpegPath = Util::config('ffmpeg_path');
        $ffprobePath = Util::config('ffprobe_path');

        return file_exists($ffmpegPath) &&
            file_exists($ffprobePath) &&
            basename($ffmpegPath) == 'ffmpeg' &&
            basename($ffprobePath) == 'ffprobe';
    }

    public static function getRequestTimeout(int $buffer = 10, int $minimum = 30): int
    {
        $maxExecutionTime = (int) ini_get('max_execution_time');

        if ($maxExecutionTime === 0) {
            return $minimum;
        }

        return max($minimum, $maxExecutionTime - $buffer);
    }
}
