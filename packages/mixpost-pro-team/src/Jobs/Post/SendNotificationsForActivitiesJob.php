<?php

namespace Inovector\Mixpost\Jobs\Post;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Models\PostActivity;
use Inovector\Mixpost\Notifications\NewPostActivity;

class SendNotificationsForActivitiesJob implements QueueWorkspaceAware, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public PostActivity $activity) {}

    public function handle(): void
    {
        if (! $this->activity->post) {
            return;
        }

        $this->getSubscribers()
            ->merge($this->getMentioned())
            ->unique('id')
            ->filter(function ($subscriber) {
                return $subscriber->id !== $this->activity->user_id;
            })
            ->each(fn ($subscriber) => $subscriber->notify(new NewPostActivity($this->activity)));
    }

    protected function getSubscribers(): Collection
    {
        return $this->activity->post->activitiesNotificationSubscriptions()
            ->with('user')
            ->get()
            ->unique('user_id')
            ->filter(fn ($subscription) => $subscription->user)
            ->map(fn ($subscription) => $subscription->user);
    }

    protected function getMentioned(): Collection
    {
        if (! $this->activity->isComment()) {
            return collect();
        }

        return $this->activity->getMentioned();
    }
}
