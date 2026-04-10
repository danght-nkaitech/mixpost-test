<?php

namespace Inovector\Mixpost\Actions\Post;

use Inovector\Mixpost\Enums\PostDeleteMode;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Events\Post\PostDeleted;
use Inovector\Mixpost\Events\Post\PostDeleteFromSocialPlatforms;
use Inovector\Mixpost\Models\Post;

class DeletePost
{
    public function __invoke(array $uuids, PostDeleteMode $mode, bool $toTrash, $userId): array
    {
        // It's important to trigger the `PostDeleteFromSocialPlatforms` event before the `PostDeleted` event,
        // so it can still access the post data in the database before it's removed.

        if ($mode === PostDeleteMode::SOCIAL_ONLY) {
            PostDeleteFromSocialPlatforms::dispatch($userId, $uuids);

            $this->dispatchEvent($uuids, $toTrash);

            return [
                'deleting_from_social_platforms' => 'in_queue',
            ];
        }

        if ($mode === PostDeleteMode::APP_AND_SOCIAL) {
            PostDeleteFromSocialPlatforms::dispatch($userId, $uuids);

            return [
                'deleting_from_social_platforms' => 'in_queue',
                'deleted_from_app' => $this->deleteRecords($uuids, $toTrash),
            ];
        }

        if ($mode === PostDeleteMode::APP_ONLY) {
            return [
                'deleted_from_app' => $this->deleteRecords($uuids, $toTrash),
            ];
        }

        return [];
    }

    protected function deleteRecords(array $uuids, bool $toTrash = true): int
    {
        $posts = Post::whereIn('uuid', $uuids);

        if ($toTrash) {
            $posts->update(['status' => PostStatus::DRAFT]);
        }

        $deleted = $toTrash ? $posts->delete() : $posts->forceDelete();

        $this->dispatchEvent($uuids, $toTrash);

        return $deleted;
    }

    protected function dispatchEvent(array $uuids, bool $toTrash = true): void
    {
        PostDeleted::dispatch($uuids, $toTrash);
    }
}
