<?php

namespace Inovector\MixpostEnterprise\Jobs\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\MixpostEnterprise\Actions\Subscription\CancelSubscription;
use Inovector\MixpostEnterprise\Models\Subscription;

class DestroySubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public readonly Subscription $subscription) {}

    public function handle(): void
    {
        (new CancelSubscription($this->subscription))->now();

        $this->subscription->delete();
    }
}
