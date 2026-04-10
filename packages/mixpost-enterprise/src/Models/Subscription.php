<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Inovector\MixpostEnterprise\Concerns\Prorates;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Exceptions\NoPaymentPlatformActiveException;
use Inovector\MixpostEnterprise\PaymentPlatform;
use Inovector\MixpostEnterprise\SubscriptionInfo;
use Inovector\MixpostEnterprise\SubscriptionPayment;
use LogicException;

class Subscription extends Model
{
    use HasFactory;
    use Prorates;

    const DEFAULT_NAME = 'default';

    public $table = 'mixpost_e_subscriptions';

    protected $fillable = [
        'name',
        'platform_subscription_id',
        'platform_plan_id',
        'status',
        'platform_data',
        'quantity',
        'trial_ends_at',
        'paused_from',
        'ends_at',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'platform_data' => 'array',
        'trial_ends_at' => 'datetime',
        'paused_from' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected ?SubscriptionInfo $subscriptionInfo = null;

    protected ?Plan $plan = null;

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'subscription_id', 'id')->orderByDesc('created_at');
    }

    public function planMonthly(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'platform_plan_id', 'monthly_platform_plan_id');
    }

    public function planYearly(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'platform_plan_id', 'yearly_platform_plan_id');
    }

    public function scopeByPlan(Builder $query, Plan $plan): void
    {
        $query->whereIn('platform_plan_id', [$plan->monthly_platform_plan_id, $plan->yearly_platform_plan_id]);
    }

    public function hasPlan(string $planId): bool
    {
        return $this->platform_plan_id == $planId;
    }

    public function plan(): ?Plan
    {
        if ($this->plan) {
            return $this->plan;
        }

        return $this->plan = Plan::findByPlatformPlanId($this->platform_plan_id);
    }

    public function valid(): bool
    {
        return $this->active() || $this->onTrial() || $this->onPausedGracePeriod() || $this->onGracePeriod();
    }

    public function active(): bool
    {
        return (is_null($this->ends_at) || $this->onGracePeriod() || $this->onPausedGracePeriod()) &&
            $this->status->value !== SubscriptionStatus::PAST_DUE->value &&
            $this->status->value !== SubscriptionStatus::PAUSED->value;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereNull('ends_at')
                ->orWhere(function ($query) {
                    $query->onGracePeriod();
                })
                ->orWhere(function ($query) {
                    $query->onPausedGracePeriod();
                });
        })->whereNotIn('status', [
            SubscriptionStatus::PAST_DUE->value,
            SubscriptionStatus::PAUSED->value,
        ]);
    }

    public function incomplete(): bool
    {
        return $this->status->value === SubscriptionStatus::INCOMPLETE->value;
    }

    public function pastDue(): bool
    {
        return $this->status->value === SubscriptionStatus::PAST_DUE->value;
    }

    public function scopePastDue(Builder $query): void
    {
        $query->where('status', SubscriptionStatus::PAST_DUE->value);
    }

    public function recurring(): bool
    {
        return ! $this->onTrial() && ! $this->paused() && ! $this->onPausedGracePeriod() && ! $this->canceled();
    }

    public function paused(): bool
    {
        return $this->status->value === SubscriptionStatus::PAUSED->value;
    }

    public function onPausedGracePeriod(): bool
    {
        return $this->paused_from && $this->paused_from->isFuture();
    }

    public function scopeOnPausedGracePeriod(Builder $query): void
    {
        $query->whereNotNull('paused_from')->where('paused_from', '>', Carbon::now());
    }

    public function canceled(): bool
    {
        return ! is_null($this->ends_at);
    }

    public function ended(): bool
    {
        return $this->canceled() && ! $this->onGracePeriod();
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function remainingTrialDays(): int
    {
        if (! $this->trial_ends_at) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays($this->trial_ends_at);
    }

    public function canBeResumed(): bool
    {
        return $this->paused() || $this->onPausedGracePeriod() || $this->onGracePeriod();
    }

    public function scopeOnTrial(Builder $query): void
    {
        $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', Carbon::now());
    }

    public function hasExpiredTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function onGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    public function scopeOnGracePeriod(Builder $query): void
    {
        $query->whereNotNull('ends_at')->where('ends_at', '>', Carbon::now());
    }

    public function swap(string $planId, array $options = []): static
    {
        $this->protectFromUpdates('swap plan');

        $platformInstance = PaymentPlatform::activePlatformInstance();

        $platformInstance->swapSubscription($this, $planId, array_merge($options, [
            'prorate' => $this->prorate,
        ]));

        $this->forceFill([
            'platform_plan_id' => $planId,
        ])->save();

        if ($plan = Plan::findByPlatformPlanId($planId)) {
            $this->workspace->saveAsAccessStatusSubscription();
            $this->workspace->removeGenericSubscription();
            $this->workspace->saveLimits($plan->limits);
        }

        $this->subscriptionInfo = null;

        return $this;
    }

    public function swapAndInvoice(string $planId, array $options = []): static
    {
        return $this->swap($planId, array_merge($options, [
            'bill_immediately' => true,
        ]));
    }

    /**
     * Cancel the subscription at the end of the current billing period.
     */
    public function cancel(): static
    {
        if ($this->onGracePeriod()) {
            return $this;
        }

        if ($this->onPausedGracePeriod() || $this->paused()) {
            $endsAt = $this->paused_from->isFuture()
                ? $this->paused_from
                : Carbon::now();
        } else {
            $endsAt = $this->onTrial()
                ? $this->trial_ends_at
                : $this->nextPayment()->date();
        }

        return $this->cancelAt($endsAt);
    }

    public function cancelNow(): static
    {
        return $this->cancelAt(Carbon::now());
    }

    public function cancelAt(Carbon $endsAt): static
    {
        $platformInstance = PaymentPlatform::activePlatformInstance();

        $platformInstance->cancelSubscription($this, $endsAt);

        $this->forceFill([
            'status' => SubscriptionStatus::CANCELED->value,
            'ends_at' => $endsAt,
        ])->save();

        $this->subscriptionInfo = null;

        return $this;
    }

    /**
     * Resume a paused or on grace period subscription
     *
     * @return $this
     *
     * @throws LogicException|NoPaymentPlatformActiveException
     */
    public function resume(): static
    {
        $platformInstance = PaymentPlatform::activePlatformInstance();

        if (! $platformInstance->supportResumeSubscription()) {
            throw new LogicException('This payment platform does not support resuming subscription.');
        }

        if (! $this->canBeResumed()) {
            throw new LogicException('Cannot resume subscription that is not paused or on grace period.');
        }

        /** @var SubscriptionInfo $subscriptionInfo */
        $subscriptionInfo = $platformInstance->resumeSubscription($this);

        $this->forceFill([
            'status' => $subscriptionInfo->status,
            'paused_from' => null,
            'ends_at' => null,
        ])->save();

        $this->subscriptionInfo = null;

        return $this;
    }

    public function portalUrl(): string
    {
        return $this->subscriptionInfo()->portalUrl;
    }

    public function lastPayment(): ?SubscriptionPayment
    {
        return $this->subscriptionInfo()->lastPayment;
    }

    public function nextPayment(): ?SubscriptionPayment
    {
        return $this->subscriptionInfo()->nextPayment;
    }

    public function subscriptionInfo(): SubscriptionInfo
    {
        if ($this->subscriptionInfo) {
            return $this->subscriptionInfo;
        }

        $platformInstance = PaymentPlatform::activePlatformInstance();

        return $this->subscriptionInfo = $platformInstance->subscriptionInfo($this);
    }

    public function protectFromUpdates(string $action): void
    {
        if ($this->incomplete()) {
            throw new LogicException("Cannot $action while incomplete.");
        }

        if ($this->onTrial()) {
            throw new LogicException("Cannot $action while on trial.");
        }

        if ($this->paused() || $this->onPausedGracePeriod()) {
            throw new LogicException("Cannot $action for paused subscriptions.");
        }

        if ($this->canceled() || $this->onGracePeriod()) {
            throw new LogicException("Cannot $action for canceled subscriptions.");
        }

        if ($this->pastDue()) {
            throw new LogicException("Cannot $action for past due subscriptions.");
        }
    }
}
