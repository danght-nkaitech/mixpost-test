<?php

namespace Inovector\MixpostEnterprise\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Inovector\MixpostEnterprise\Enums\PlanType;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\SubscriptionBuilder;

trait ManagesSubscriptions
{
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'workspace_id', 'id')->orderByDesc('created_at');
    }

    public function genericSubscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'generic_subscription_plan_id', 'id');
    }

    public function subscription($name = 'default'): ?Model
    {
        return $this->subscriptions->where('name', $name)->first();
    }

    public function newSubscription($name, $planId): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $name, $planId);
    }

    public function onTrial($name = 'default', $plan = null)
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->onTrial()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    public function hasExpiredTrial($name = 'default', $plan = null)
    {
        if (func_num_args() === 0 && $this->hasExpiredGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->hasExpiredTrial()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    public function hasGenericSubscription(): bool
    {
        return $this->generic_subscription_plan_id !== null;
    }

    public function genericSubscriptionFree(): bool
    {
        return $this->generic_subscription_free;
    }

    public function scopeGenericSubscriptionByPlan(Builder $query, int|Plan $plan): Builder
    {
        $planId = $plan instanceof Plan ? $plan->id : $plan;

        return $query->where('generic_subscription_plan_id', $planId);
    }

    public function scopeGenericSubscriptionFree(Builder $query): Builder
    {
        return $query->where('generic_subscription_free', true);
    }

    public function hasGenericTrial(): bool
    {
        return $this->generic_trial_ends_at !== null;
    }

    public function onGenericTrial(): bool
    {
        return $this->generic_trial_ends_at && $this->generic_trial_ends_at->isFuture();
    }

    public function hasExpiredGenericTrial(): bool
    {
        return $this->generic_trial_ends_at && $this->generic_trial_ends_at->isPast();
    }

    public function remainingGenericTrialDays(): int
    {
        if (! $this->generic_trial_ends_at) {
            return 0;
        }

        if ($this->hasExpiredGenericTrial()) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays($this->generic_trial_ends_at);
    }

    public function setGenericSubscription(Plan $plan, ?Carbon $trialEndsAt = null): void
    {
        $this->generic_subscription_plan_id = $plan->id;
        $this->generic_subscription_free = $plan->type->value === PlanType::FREE->value;
        $this->generic_trial_ends_at = $trialEndsAt;
        $this->save();
    }

    public function removeGenericSubscription(bool $keepPrevTrialEndsAt = true): void
    {
        $this->generic_subscription_plan_id = null;
        $this->generic_subscription_free = false;

        if (! $keepPrevTrialEndsAt) {
            $this->generic_trial_ends_at = null;
        }

        $this->save();
    }

    public function trialEndsAt($name = 'default')
    {
        if ($subscription = $this->subscription($name)) {
            return $subscription->trial_ends_at;
        }

        return $this->generic_trial_ends_at;
    }

    public function subscribed($name = 'default', $plan = null)
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }
}
