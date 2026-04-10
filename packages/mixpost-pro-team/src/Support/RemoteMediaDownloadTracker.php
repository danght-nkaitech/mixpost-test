<?php

namespace Inovector\Mixpost\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inovector\Mixpost\Enums\RemoteMediaDownloadStatus;

class RemoteMediaDownloadTracker
{
    protected const CACHE_PREFIX = 'mixpost_remote_download_';

    protected const CACHE_TTL = 3600; // 1 hour

    public static function create(string $downloadId, string $url, int $workspaceId): void
    {
        Cache::put(self::cacheKey($downloadId), [
            'status' => RemoteMediaDownloadStatus::PENDING->value,
            'url' => $url,
            'workspace_id' => $workspaceId,
            'media_id' => null,
            'error' => null,
            'progress' => 0,
            'created_at' => Carbon::now()->toIso8601String(),
            'updated_at' => Carbon::now()->toIso8601String(),
        ], self::CACHE_TTL);
    }

    public static function updateStatus(string $downloadId, RemoteMediaDownloadStatus $status, ?int $progress = null): void
    {
        $data = self::get($downloadId);

        if (! $data) {
            return;
        }

        $data['status'] = $status->value;
        $data['updated_at'] = Carbon::now()->toIso8601String();

        if ($progress !== null) {
            $data['progress'] = $progress;
        }

        Cache::put(self::cacheKey($downloadId), $data, self::CACHE_TTL);
    }

    public static function markCompleted(string $downloadId, string $mediaId): void
    {
        $data = self::get($downloadId);

        if (! $data) {
            return;
        }

        $data['status'] = RemoteMediaDownloadStatus::COMPLETED->value;
        $data['media_id'] = $mediaId;
        $data['progress'] = 100;
        $data['updated_at'] = Carbon::now()->toIso8601String();

        Cache::put(self::cacheKey($downloadId), $data, self::CACHE_TTL);
    }

    public static function markFailed(string $downloadId, string $error): void
    {
        $data = self::get($downloadId);

        if (! $data) {
            return;
        }

        $data['status'] = RemoteMediaDownloadStatus::FAILED->value;
        $data['error'] = $error;
        $data['updated_at'] = Carbon::now()->toIso8601String();

        Cache::put(self::cacheKey($downloadId), $data, self::CACHE_TTL);
    }

    public static function get(string $downloadId): ?array
    {
        return Cache::get(self::cacheKey($downloadId));
    }

    public static function delete(string $downloadId): void
    {
        Cache::forget(self::cacheKey($downloadId));
    }

    protected static function cacheKey(string $downloadId): string
    {
        return self::CACHE_PREFIX.$downloadId;
    }
}
