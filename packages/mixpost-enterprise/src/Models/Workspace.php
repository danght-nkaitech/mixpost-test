<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Models\Workspace as WorkspaceCoreModel;
use Inovector\MixpostEnterprise\Casts\PlanLimits;
use Inovector\MixpostEnterprise\Concerns\ManagesSubscriptions;
use Inovector\MixpostEnterprise\Enums\WorkspaceAccessStatus;

class Workspace extends WorkspaceCoreModel
{
    use ManagesSubscriptions;
    use UsesUserModel;

    protected $fillable = [
        'uuid',
        'name',
        'hex_color',
        'owner_id',
        'access_status',
        'generic_subscription_plan_id',
        'generic_subscription_free',
        'generic_trial_ends_at',
        'pm_type',
        'pm_card_brand',
        'pm_card_last_four',
        'pm_card_expires',
        'limits',
    ];

    protected $casts = [
        'access_status' => WorkspaceAccessStatus::class,
        'generic_subscription_free' => 'boolean',
        'generic_trial_ends_at' => 'datetime',
        'limits' => PlanLimits::class,
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'owner_id', 'id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'workspace_id', 'id')->orderByDesc('created_at');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'workspace_id', 'id')->orderByDesc('created_at');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'workspace_id');
    }

    public function scopeOwnedBy(Builder $query, Authenticatable|User $user): void
    {
        $query->where('owner_id', $user->id);
    }

    public function saveOwner(Authenticatable|User $user): void
    {
        $this->owner_id = $user->id;
        $this->save();
    }

    public function deleteOwner(): void
    {
        $this->owner_id = null;
        $this->save();
    }

    public function isOwner(Authenticatable|User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function saveAsAccessStatusSubscription(): void
    {
        $this->access_status = WorkspaceAccessStatus::SUBSCRIPTION;
        $this->save();
    }

    public function unlimitedAccess(): bool
    {
        return $this->access_status == WorkspaceAccessStatus::UNLIMITED;
    }

    public function scopeUnlimitedAccess(Builder $query): void
    {
        $query->where('access_status', WorkspaceAccessStatus::UNLIMITED);
    }

    public function locked(): bool
    {
        return $this->access_status == WorkspaceAccessStatus::LOCKED;
    }

    public function scopeLocked(Builder $query): void
    {
        $query->where('access_status', WorkspaceAccessStatus::LOCKED);
    }

    public function scopeUnlocked(Builder $query): void
    {
        $query->whereNot('access_status', WorkspaceAccessStatus::LOCKED);
    }

    public function saveLimits(array $limits): void
    {
        $this->limits = $limits;
        $this->save();
    }

    public function savePaymentMethod(string $type, ?string $cardBrand, ?string $cardLastFour, ?string $cardExpires): void
    {
        $this->pm_type = $type;
        $this->pm_card_brand = $cardBrand;
        $this->pm_card_last_four = $cardLastFour;
        $this->pm_card_expires = $cardExpires;

        $this->save();
    }

    public function removePaymentMethod(): void
    {
        $this->pm_type = null;
        $this->pm_card_brand = null;
        $this->pm_card_last_four = null;
        $this->pm_card_expires = null;

        $this->save();
    }

    public function usedStorage()
    {
        return Media::byWorkspace($this->id)->sum('size_total');
    }

    public function valid(): bool
    {
        if ($this->locked()) {
            return false;
        }

        if ($this->unlimitedAccess()) {
            return true;
        }

        if ($this->hasGenericSubscription() && $this->hasGenericTrial() && ! $this->genericSubscriptionFree()) {
            return $this->onGenericTrial();
        }

        return $this->hasGenericSubscription() || (bool) $this->subscription()?->valid();
    }
}
