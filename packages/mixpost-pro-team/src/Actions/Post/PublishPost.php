<?php

namespace Inovector\Mixpost\Actions\Post;

use Illuminate\Support\Facades\Bus;
use Inovector\Mixpost\Jobs\AccountPublishPostJob;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\Post;

class PublishPost
{
    public function __invoke(Post $post): void
    {
        if ($post->isScheduleProcessing()) {
            return;
        }

        $post->setScheduleProcessing();

        $jobs = $post->accounts->map(function (Account $account) use ($post) {
            return new AccountPublishPostJob($account, $post);
        });

        Bus::batch($jobs)
            ->allowFailures()
            ->finally(function () use ($post) {
                if ($post->hasErrors()) {
                    $post->setFailed();

                    return;
                }

                if ($post->isScheduleProcessing()) {
                    $post->setPublished();
                }
            })
            ->onQueue('publish-post')
            ->dispatch();
    }
}
