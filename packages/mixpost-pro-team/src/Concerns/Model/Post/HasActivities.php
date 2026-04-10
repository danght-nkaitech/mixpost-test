<?php

namespace Inovector\Mixpost\Concerns\Model\Post;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Enums\PostActivityType;
use Inovector\Mixpost\Events\Post\PostActivityCreated;
use Inovector\Mixpost\Models\PostActivity;

trait HasActivities
{
    use ManagesComments;

    public function activities(): HasMany
    {
        return $this->hasMany(PostActivity::class, 'post_id', 'id');
    }

    public function logActivity(int|User $user, PostActivityType $type, array $data = []): PostActivity
    {
        $activity = $this->activities()->create([
            'type' => $type,
            'user_id' => $user instanceof User ? $user->id : $user,
            'data' => $data,
        ]);

        PostActivityCreated::dispatch($activity);

        return $activity;
    }

    public function logCreatedActivity(): PostActivity
    {
        return $this->logActivity($this->user_id, PostActivityType::CREATED);
    }

    public function logSetDraftActivity(int|User $user): PostActivity
    {
        return $this->logActivity($user, PostActivityType::SET_DRAFT);
    }

    public function logScheduledActivity(int|User $user, array $data): PostActivity
    {
        return $this->logActivity($user, PostActivityType::SCHEDULED, $data);
    }

    public function logUpdatedScheduleTimeActivity(int|User $user, array $data): PostActivity
    {
        return $this->logActivity($user, PostActivityType::UPDATED_SCHEDULE_TIME, $data);
    }

    public function logScheduleProcessing(): PostActivity
    {
        return $this->logActivity(0, PostActivityType::SCHEDULE_PROCESSING);
    }

    public function logPublishedActivity(): PostActivity
    {
        return $this->logActivity(0, PostActivityType::PUBLISHED);
    }

    public function logPublishedFailedActivity(): PostActivity
    {
        return $this->logActivity(0, PostActivityType::PUBLISHED_FAILED);
    }

    public function logDeletedFromSocialPlatformActivity(int|User $user, array $data): PostActivity
    {
        return $this->logActivity($user, PostActivityType::DELETED_FROM_SOCIAL_PLATFORMS, $data);
    }
}
