<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesPostActivities;
use Inovector\Mixpost\Events\Post\PostCommentUpdated;
use Inovector\Mixpost\Models\PostActivity;

class UpdatePostComment extends PostFormRequest
{
    use UsesPostActivities;

    public ?PostActivity $activity;

    public function rules(): array
    {
        return [
            'text' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->activity = self::getActivity(
                post: $this->route('post'),
                activityUuid: $this->route('activity')
            );

            $isAuthor = $this->activity?->user_id === Auth::id();

            if ($this->activity && ! $isAuthor) {
                $validator->errors()->add('user', 'You are not allowed to update this comment.');
            }
        });
    }

    public function handle(): bool
    {
        if (! $this->activity || ! $this->activity->isComment()) {
            abort(404);
        }

        $update = $this->activity->update([
            'text' => $this->input('text'),
        ]);

        if ($update) {
            PostCommentUpdated::broadcast($this->activity)->toOthers();
        }

        return $update;
    }
}
