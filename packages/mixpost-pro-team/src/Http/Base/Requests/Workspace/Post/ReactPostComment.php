<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesPostActivities;
use Inovector\Mixpost\Enums\PostCommentReactionToggleType;
use Inovector\Mixpost\Events\Post\PostCommentReactionsUpdated;

class ReactPostComment extends PostFormRequest
{
    use UsesPostActivities;

    public function rules(): array
    {
        return [
            'reaction' => ['required', 'string'],
        ];
    }

    public function handle(): PostCommentReactionToggleType
    {
        $activity = self::getActivity(
            post: $this->route('post'),
            activityUuid: $this->route('activity')
        );

        if (! $activity || ! $activity->isComment()) {
            abort(404);
        }

        $toggleType = $activity->toggleReaction(
            user: Auth::id(),
            reaction: $this->input('reaction')
        );

        PostCommentReactionsUpdated::broadcast($activity)->toOthers();

        return $toggleType;
    }
}
