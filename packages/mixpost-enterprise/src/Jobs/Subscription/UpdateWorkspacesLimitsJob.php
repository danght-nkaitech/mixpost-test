<?php

namespace Inovector\MixpostEnterprise\Jobs\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;

class UpdateWorkspacesLimitsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public readonly Plan $plan) {}

    public function handle(): void
    {
        $this->updateForSubscriptionPlan();
        $this->updateForGenericSubscriptionPlan();
    }

    private function updateForSubscriptionPlan(): void
    {
        Workspace::whereHas('subscriptions', function ($query) {
            $query->byPlan($this->plan);
        })->update(['limits' => $this->plan->limits]);
    }

    private function updateForGenericSubscriptionPlan(): void
    {
        Workspace::query()
            ->has('genericSubscriptionPlan')
            ->where('generic_subscription_plan_id', $this->plan->id)
            ->update(['limits' => $this->plan->limits]);
    }
}
