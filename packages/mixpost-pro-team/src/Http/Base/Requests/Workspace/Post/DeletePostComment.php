<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesPostActivities;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Events\Post\PostCommentDeleted;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\PostActivity;

class DeletePostComment extends PostFormRequest
{
    use UsesPostActivities;

    public ?PostActivity $activity;

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->activity = self::getActivity(
                post: $this->route('post'),
                activityUuid: $this->route('activity')
            );

            $isAuthor = $this->activity?->user_id === Auth::id();
            $isAdmin = Auth::user()->hasWorkspace(WorkspaceManager::current(), WorkspaceUserRole::ADMIN);

            if ($this->activity && ! $isAuthor && ! $isAdmin) {
                $validator->errors()->add('user', 'You are not allowed to update this comment.');
            }
        });
    }

    public function handle(): bool
    {
        if (! $this->activity || ! $this->activity->isComment()) {
            abort(404);
        }

        $delete = $this->activity->delete();

        if ($delete) {
            PostCommentDeleted::broadcast($this->route('post'), $this->route('activity'))->toOthers();
        }

        return $delete;
    }
}
