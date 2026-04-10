<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\SubscriptionInfo;
use Inovector\MixpostEnterprise\SubscriptionPayment;
use LogicException;

trait ManagesSubscriptions
{
    use UsesAuth;

    public function createSubscription(Workspace $workspace, string $planId, array $payload): string
    {
        $customer = $this->createAsCustomer($workspace);

        return view('mixpost-enterprise::payment_platforms.paddle_billing', [
            'price_id' => $planId,
            'customer' => $customer,
            'discount_code' => Arr::get($payload, 'coupon_code'),
            'success_url' => Arr::get($payload, 'return_url'),
            'sandbox' => filter_var(
                Arr::get($this->options, 'sandbox', false), FILTER_VALIDATE_BOOLEAN
            ),
            'client_side_token' => $this->credentials['client_side_token'] ?? '',
            'custom_data' => [
                'workspace_uuid' => $workspace->uuid,
                'subscription_type' => Subscription::DEFAULT_NAME,
            ],
        ])->render();
    }

    public function subscriptionInfo(Subscription $subscription): SubscriptionInfo
    {
        $result = $this->makeApiCall('get', "/subscriptions/$subscription->platform_subscription_id", [
            'include' => 'next_transaction',
        ]);

        $data = $result['data'];

        $info = (new SubscriptionInfo)
            ->setRaw($data)
            ->setStatus($this->mapStatus($data['status']))
            ->setPortalUrl($data['management_urls']['update_payment_method']);

        if ($transaction = $data['next_transaction'] ?? null) {
            $info->setNextPayment(new SubscriptionPayment(
                $transaction['details']['totals']['grand_total'],
                $transaction['details']['totals']['currency_code'],
                Carbon::parse($transaction['billing_period']['starts_at'], 'UTC')
            ));
        }

        return $info;
    }

    public function swapSubscription(Subscription $subscription, string $newPlanId, array $payload): SubscriptionInfo
    {
        $prorate = $payload['prorate'] ?? true;
        $billImmediately = $payload['bill_immediately'] ?? true;

        $result = $this->makeApiCall('patch', "/subscriptions/$subscription->platform_subscription_id", [
            'items' => [
                [
                    'price_id' => $newPlanId,
                    'quantity' => 1,
                ],
            ],
            'proration_billing_mode' => $billImmediately ?
                ($prorate ? 'prorated_immediately' : 'full_immediately') :
                ($prorate ? 'prorated_next_billing_period' : 'full_next_billing_period'),
        ]);

        $data = $result['data'];

        return (new SubscriptionInfo)
            ->setRaw($data)
            ->setStatus($this->mapStatus($data['status']))
            ->setPortalUrl($data['management_urls']['update_payment_method']);
    }

    public function cancelSubscription(Subscription $subscription, Carbon $endsAt): SubscriptionInfo
    {
        $result = $this->makeApiCall('post', "/subscriptions/$subscription->platform_subscription_id/cancel", [
            'effective_from' => $endsAt->isToday() ? 'immediately' : 'next_billing_period',
        ]);

        $data = $result['data'];

        return (new SubscriptionInfo)
            ->setRaw($data)
            ->setStatus($this->mapStatus($data['status']))
            ->setPortalUrl($data['management_urls']['update_payment_method']);
    }

    public function resumeSubscription(Subscription $subscription, array $options = []): SubscriptionInfo
    {
        if ($subscription->paused()) {
            $result = $this->makeApiCall('post', "/subscriptions/$subscription->platform_subscription_id/resume", [
                'effective_from' => 'immediately',
            ]);

        } elseif ($subscription->onPausedGracePeriod() || $subscription->onGracePeriod()) {
            $result = $this->makeApiCall('patch', "/subscriptions/$subscription->platform_subscription_id", [
                'scheduled_change' => null,
            ]);
        } else {
            throw new LogicException('Cannot resume subscription that is not paused or on grace period.');
        }

        $data = $result['data'];

        return (new SubscriptionInfo)
            ->setRaw($data)
            ->setStatus($this->mapStatus($data['status']))
            ->setPortalUrl($data['management_urls']['update_payment_method']);
    }
}
