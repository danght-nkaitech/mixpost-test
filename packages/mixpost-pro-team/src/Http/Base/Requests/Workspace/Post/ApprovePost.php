<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Events\Post\PostScheduled;
use Inovector\Mixpost\Events\Post\SchedulingPost;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Post;

class ApprovePost extends FormRequest
{
    public Post $post;

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator)
    {
        $this->post = Post::firstOrFailByUuid($this->route('post'));

        $validator->after(function ($validator) {
            if (! Auth::user()->canApprove(WorkspaceManager::current())) {
                $validator->errors()->add('user_can_not_approve', 'user_can_not_approve');
            }

            if (! $this->post->isNeedsApproval()) {
                $validator->errors()->add('needs_approval', __('mixpost::post.needs_approval'));
            }

            if ($this->post->isInHistory()) {
                $validator->errors()->add('in_history', 'in_history');
            }

            if ($this->post->isScheduleProcessing()) {
                $validator->errors()->add('publishing', 'publishing');
            }

            if (! $this->post->canSchedule()) {
                $validator->errors()->add('cannot_scheduled', __('mixpost::post.post_cannot_scheduled')."\n".__('mixpost::post.past_date'));
            }
        });
    }

    public function handle(): void
    {
        SchedulingPost::dispatch($this->post, $this);

        $this->post->setScheduled($this->getDateTime());

        PostScheduled::dispatch($this->post, true);
    }

    public function getDateTime(): Carbon|\Carbon\Carbon
    {
        return $this->post->scheduled_at;
    }
}
