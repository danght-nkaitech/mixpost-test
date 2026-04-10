<?php

namespace Inovector\Mixpost\Concerns\Model\Post;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Models\PostActivitiesNotificationSubscription;

trait HasActivitiesNotificationSubscriptions
{
    public function activitiesNotificationSubscriptions(): HasMany
    {
        return $this->hasMany(PostActivitiesNotificationSubscription::class, 'post_id');
    }

    public function subscribeToActivitiesNotifications(int|User $user): PostActivitiesNotificationSubscription
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->activitiesNotificationSubscriptions()->create([
            'user_id' => $userId,
        ]);
    }

    public function unsubscribeFromActivitiesNotifications(int|User $user): int
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->activitiesNotificationSubscriptions()
            ->where('user_id', $userId)
            ->delete();
    }

    public function hasNotificationSubscriptionForActivities(int|User $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->activitiesNotificationSubscriptions()
            ->where('user_id', $userId)
            ->exists();
    }
}
