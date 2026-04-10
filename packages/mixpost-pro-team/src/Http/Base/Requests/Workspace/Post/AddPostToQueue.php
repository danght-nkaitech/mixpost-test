<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Concerns\Approval;
use Inovector\Mixpost\Events\Post\PostScheduled;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\PostingSchedule;

class AddPostToQueue extends FormRequest
{
    use Approval;

    public Post $post;

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator)
    {
        $this->post = Post::firstOrFailByUuid($this->route('post'));

        $validator->after(function ($validator) {
            if ($this->post->isInHistory()) {
                $validator->errors()->add('in_history', 'in_history');
            }

            if ($this->post->isScheduleProcessing()) {
                $validator->errors()->add('publishing', 'publishing');
            }

            if (! $this->post->accounts()->exists()) {
                $validator->errors()->add('cannot_scheduled', __('mixpost::post.post_cannot_scheduled')."\n".__('mixpost::post.accounts_not_selected'));
            }

            if (! PostingSchedule::hasAvailableTimes()) {
                $validator->errors()->add('available_times', __('mixpost::post.posting_schedule_not_available_times'));
            }
        });
    }

    public function handle(): Post
    {
        $this->post->setScheduled(PostingSchedule::getNextScheduleDateTime(), $this->determineSchedulePostStatus());

        PostScheduled::dispatch($this->post);

        $this->post->refresh();

        return $this->post;
    }
}
