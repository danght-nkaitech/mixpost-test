<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Events\Post\PostScheduleAtUpdated;
use Inovector\Mixpost\Events\Post\PostSetDraft;
use Inovector\Mixpost\Events\Post\PostUpdated;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Util;

class UpdatePost extends PostFormRequest
{
    public Post $post;

    public function withValidator($validator): void
    {
        $this->post = Post::firstOrFailByUuid($this->route('post'));

        $validator->after(function ($validator) {
            if ($this->post->isInHistory()) {
                $validator->errors()->add('in_history', 'in_history');
            }

            if ($this->post->isScheduleProcessing()) {
                $validator->errors()->add('publishing', 'publishing');
            }
        });
    }

    public function handle(): Post
    {
        $scheduledAtOldValue = $this->post->scheduled_at;
        $isScheduledOldValue = $this->post->isScheduled();
        $wasSetDraft = false;

        DB::transaction(function () use (&$wasSetDraft) {
            if (! $this->post->isDraft() && (empty($this->input('accounts')) || ! $this->scheduledAt())) {
                $this->post->setDraft(false);
                $wasSetDraft = true;
            }

            $this->post->accounts()->sync($this->input('accounts'));
            $this->post->tags()->sync($this->input('tags'));

            $this->post->versions()->delete();
            $this->post->versions()->createMany($this->inputVersions());

            $this->post->setScheduled(
                datetime: $this->scheduledAt() ? Util::convertTimeToUTC($this->scheduledAt()) : null,
                status: null,
            );
        });

        PostUpdated::dispatch($this->post);

        if ($this->post->scheduledAtWasChanged() && $isScheduledOldValue && ! $wasSetDraft) {
            PostScheduleAtUpdated::dispatch($this->post, $scheduledAtOldValue);
        }

        if ($wasSetDraft && $this->post->isDraft()) {
            PostSetDraft::dispatch($this->post);
        }

        return $this->post;
    }

    public function getPost(): Post
    {
        return $this->post;
    }
}
