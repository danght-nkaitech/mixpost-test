<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\Approval;
use Inovector\Mixpost\Events\Post\PostScheduled;
use Inovector\Mixpost\Events\Post\SchedulingPost;
use Inovector\Mixpost\Models\Post;

class SchedulePost extends FormRequest
{
    use Approval;

    public Post $post;

    public function rules(): array
    {
        return [
            'postNow' => ['required', 'boolean'],
        ];
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

            if ($this->input('postNow')) {
                // Add the current time + 1 minute for the `scheduled_at` field without save it into database.
                // canSchedule method require that the `scheduled_at` field is not null and not in the past.
                $this->post->setAttribute('scheduled_at', Carbon::now()->utc()->addMinute());
            }

            if (! $this->post->canSchedule()) {
                $validator->errors()->add('cannot_scheduled', __('mixpost::post.post_cannot_scheduled')."\n".__('mixpost::post.past_date'));
            }
        });
    }

    public function handle(): void
    {
        SchedulingPost::dispatch($this->post, $this);

        $this->post->setScheduled($this->getDateTime(), $this->determineSchedulePostStatus());

        PostScheduled::dispatch($this->post);
    }

    public function getDateTime(): Carbon|\Carbon\Carbon
    {
        return $this->input('postNow') ? Carbon::now()->utc() : $this->post->scheduled_at;
    }

    public function getPost(): Post
    {
        return $this->post;
    }
}
