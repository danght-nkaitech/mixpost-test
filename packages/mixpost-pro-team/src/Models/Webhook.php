<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Inovector\Mixpost\Actions\Webhook\TriggerWebhook;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Enums\WebhookDeliveryStatus;
use Inovector\Mixpost\Enums\WebhookMethod;

class Webhook extends Model
{
    use HasFactory;
    use HasUuid;
    use UsesWorkspaceModel;

    public $table = 'mixpost_webhooks';

    protected $fillable = [
        'uuid',
        'workspace_id',
        'name',
        'callback_url',
        'method',
        'content_type',
        'max_attempts',
        'last_delivery_status',
        'secret',
        'active',
        'events',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $casts = [
        'method' => WebhookMethod::class,
        'max_attempts' => 'integer',
        'last_delivery_status' => WebhookDeliveryStatus::class,
        'secret' => 'encrypted',
        'events' => 'array',
        'active' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(self::getWorkspaceModelClass(), 'workspace_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function scopeByWorkspace(Builder $query, int|Model $workspace): void
    {
        $query->where('workspace_id', $workspace instanceof Model ? $workspace->id : $workspace);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->whereNull('workspace_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function isSystem(): bool
    {
        return $this->workspace_id === null;
    }

    public function isForWorkspace(int|Model $workspace): bool
    {
        return $this->workspace_id === ($workspace instanceof Model ? $workspace->id : $workspace);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isSucceeded(): bool
    {
        return $this->last_delivery_status === WebhookDeliveryStatus::SUCCESS;
    }

    public function updateLastDeliveryStatus(WebhookDeliveryStatus $status): void
    {
        $this->last_delivery_status = $status;
        $this->save();
    }

    public function trigger(WebhookEvent $event): Model|WebhookDelivery
    {
        return (new TriggerWebhook)($this, $event);
    }
}
