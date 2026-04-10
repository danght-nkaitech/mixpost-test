<?php

namespace Inovector\Mixpost\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\Mixpost\Enums\WebhookDeliveryStatus;
use Inovector\Mixpost\Support\AnonymousWebhookEvent;

class WebhookDelivery extends Model
{
    use HasFactory;
    use HasUuid;
    use MassPrunable;

    public $table = 'mixpost_webhook_deliveries';

    public $timestamps = false;

    protected $fillable = [
        'event',
        'attempts',
        'status',
        'http_status',
        'resend_at',
        'resent_manually',
        'payload',
        'response',
        'created_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'status' => WebhookDeliveryStatus::class,
        'http_status' => 'integer',
        'resend_at' => 'datetime',
        'resent_manually' => 'boolean',
        'payload' => 'array',
        'response' => 'array',
        'created_at' => 'datetime',
    ];

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', Carbon::now()->subMonth());
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function isSucceeded(): bool
    {
        return $this->status === WebhookDeliveryStatus::SUCCESS;
    }

    public function isAttemptLimitReached(): bool
    {
        return $this->attempts >= $this->webhook->max_attempts;
    }

    public function attempt(): void
    {
        $this->increment('attempts');
    }

    public function setAsResentManually(): void
    {
        $this->resent_manually = true;
        $this->save();
    }

    public function updateResendAt(Carbon|DateTimeInterface|null $datetime = null): void
    {
        $this->resend_at = $datetime;
        $this->save();
    }

    public function resend(): void
    {
        $event = (new AnonymousWebhookEvent($this->event, $this->payload['data'] ?? []));

        $this->webhook->trigger($event);
    }
}
