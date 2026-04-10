<?php

namespace Inovector\Mixpost\Concerns;

use Illuminate\Support\Carbon;
use Inovector\Mixpost\Exceptions\CurrentWorkspaceCouldNotBeDetermined;
use Inovector\Mixpost\Facades\WorkspaceManager;

trait UsesMediaPath
{
    /**
     * Get the media workspace path without any subdirectory.
     *
     * @throws CurrentWorkspaceCouldNotBeDetermined
     */
    public static function mediaWorkspacePath(): string
    {
        return rtrim(static::getWorkspaceUuid(), '/');
    }

    /**
     * Get the media workspace path with the default subdirectory (e.g., "m-Y").
     *
     * @throws CurrentWorkspaceCouldNotBeDetermined
     */
    public static function mediaWorkspacePathWithDateSubpath(): string
    {
        return static::mediaWorkspacePathWithSubpath('uploads/'.Carbon::now()->format('m-Y'));
    }

    /**
     * Get the media workspace path with the "avatars" subdirectory.
     *
     * @throws CurrentWorkspaceCouldNotBeDetermined
     */
    public static function mediaWorkspacePathWithAvatarsSubpath(): string
    {
        return static::mediaWorkspacePathWithSubpath('avatars');
    }

    /**
     * Get the media workspace path with a custom subdirectory.
     *
     * @throws CurrentWorkspaceCouldNotBeDetermined
     */
    public static function mediaWorkspacePathWithSubpath(string $subdirectory): string
    {
        return static::mediaWorkspacePath().'/'.ltrim($subdirectory, '/');
    }

    /**
     * Get the current workspace UUID.
     *
     * @throws CurrentWorkspaceCouldNotBeDetermined
     */
    private static function getWorkspaceUuid(): string
    {
        $workspace = WorkspaceManager::current();

        if (! $workspace) {
            throw new CurrentWorkspaceCouldNotBeDetermined;
        }

        return $workspace->uuid;
    }
}
