<?php

namespace Inovector\MixpostEnterprise\Listeners\Post;

use Inovector\MixpostEnterprise\FeatureLimitResources\ScheduledPosts;

class ValidateLimitsOnSchedulingPost
{
    public function handle(object $event): void
    {
        if ($event->workspace->unlimitedAccess()) {
            return;
        }

        app(ScheduledPosts::class)
            ->limits($event->workspace->limits)
            ->validator($event)
            ->validate();
    }
}
