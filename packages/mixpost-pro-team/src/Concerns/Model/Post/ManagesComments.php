<?php

namespace Inovector\Mixpost\Concerns\Model\Post;

use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Enums\PostActivityType;
use Inovector\Mixpost\Events\Post\PostActivityCreated;
use Inovector\Mixpost\Events\Post\PostCommentUpdated;
use Inovector\Mixpost\Models\PostActivity;
use LogicException;

trait ManagesComments
{
    public function storeComment(int|User $user, string $text, int|null|PostActivity $parent = null): PostActivity
    {
        $parentModel = $parent instanceof PostActivity ? $parent : $this->activities()->find($parent);

        if ($parentModel && ! $parentModel->isComment()) {
            throw new LogicException('Parent activity is not a comment');
        }

        $activity = $this->activities()->create([
            'user_id' => $user instanceof User ? $user->id : $user,
            'parent_id' => $parentModel?->id,
            'type' => PostActivityType::COMMENT,
            'text' => $text,
        ]);

        PostActivityCreated::broadcast($activity)->toOthers();

        if ($parentModel) {
            PostCommentUpdated::broadcast($parentModel)->toOthers();
        }

        return $activity;
    }

    public function replyToComment(int|User $user, string $text, int $commentId): PostActivity
    {
        return $this->storeComment($user, $text, $commentId);
    }
}
