<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Billable;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Util;
use Inovector\MixpostEnterprise\SubscriptionInfo;
use Inovector\MixpostEnterprise\SubscriptionPayment;
use LogicException;
use Stripe\Subscription as StripeSubscription;

trait ManagesSubscriptions
{
    use UsesAuth;

    public function createSubscription(Workspace $workspace, string $planId, array $payload): array
    {
        $billable = new Billable($this->credentials, $workspace);

        $customer = $billable->createOrGetCustomer([
            'name' => self::getAuthGuard()->user()->name,
            'email' => self::getAuthGuard()->user()->email,
        ]);

        $trialEnd = null;

        if (! is_null($payload['trial_days'])) {
            $minimumTrialPeriod = Carbon::now('UTC')->addHours(48)->addSeconds(10);
            $trialExpires = Carbon::now('UTC')->addDays((int) $payload['trial_days'])->endOfDay();

            $trialEnd = $trialExpires->gt($minimumTrialPeriod) ? $trialExpires : $minimumTrialPeriod;
        }

        $data = [
            'mode' => 'subscription',
            'line_items' => [
                [
                    'price' => $planId,
                    'quantity' => 1,
                ],
            ],
            'subscription_data' => array_filter([
                'trial_end' => $trialEnd?->getTimestamp(),
                'metadata' => array_merge($payload['metadata'] ?? [], [
                    'workspace_uuid' => $billable->workspace->uuid,
                    'is_on_session_checkout' => true,
                    'subscription_type' => Subscription::DEFAULT_NAME,
                ]),
            ]),
            'customer' => $customer->id,
            'billing_address_collection' => 'required',
            'success_url' => Arr::get($payload, 'return_url'),
            'cancel_url' => Arr::get($payload, 'cancel_url'),
        ];

        if ($couponCode = Arr::get($payload, 'coupon_code')) {
            $data['discounts'] = [
                [
                    'coupon' => $couponCode,
                ],
            ];
        }

        $session = $this->client()->checkout->sessions->create(array_filter($data));

        return [
            'redirect_to' => $session->url,
        ];
    }

    public function subscriptionInfo(Subscription $subscription): SubscriptionInfo
    {
        $stripeSubscription = $this->client()->subscriptions->retrieve($subscription->platform_subscription_id);

        $billingPortalUrl = $this->client()->billingPortal->sessions->create([
            'customer' => $subscription->workspace->stripe_id,
            'return_url' => route('mixpost_e.workspace.billing', ['workspace' => $subscription->workspace->uuid]),
        ])['url'];

        return (new SubscriptionInfo)
            ->setRaw($stripeSubscription->toArray())
            ->setStatus(Util::mapStatus($subscription->status->value))
            ->setNextPayment(new SubscriptionPayment(
                0,
                'USD',
                Carbon::createFromTimestamp($stripeSubscription->current_period_end)
            ))
            ->setPortalUrl($billingPortalUrl);
    }

    public function swapSubscription(Subscription $subscription, string $newPlanId, array $payload): SubscriptionInfo
    {
        $items = $this->mergeItemsThatShouldBeDeletedDuringSwap(
            $this->asStripeSubscription($subscription),
            $this->parseSwapPrices([$newPlanId])
        );

        $prorate = $payload['prorate'] ? 'create_prorations' : 'none';

        $prorateBehavior = isset($payload['bill_immediately'])
            ? ($payload['bill_immediately'] ? 'always_invoice' : $prorate)
            : $prorate;

        $result = $this->client()->subscriptions->update($subscription->platform_subscription_id, [
            'items' => $items->values()->all(),
            'payment_behavior' => StripeSubscription::PAYMENT_BEHAVIOR_ERROR_IF_INCOMPLETE,
            'promotion_code' => null,
            'proration_behavior' => $prorateBehavior,
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        return (new SubscriptionInfo)
            ->setRaw($result->toArray())
            ->setStatus(Util::mapStatus($result->status));
    }

    public function cancelSubscription(Subscription $subscription, Carbon $endsAt): SubscriptionInfo
    {
        if ($endsAt->isToday()) {
            $result = $this->client()->subscriptions->cancel($subscription->platform_subscription_id);
        } else {
            $result = $this->client()->subscriptions->update($subscription->platform_subscription_id, [
                'cancel_at_period_end' => true,
            ]);
        }

        return (new SubscriptionInfo)
            ->setRaw($result->toArray())
            ->setStatus(Util::mapStatus($result->status));
    }

    public function resumeSubscription(Subscription $subscription, array $options = []): SubscriptionInfo
    {
        if (! $subscription->onGracePeriod()) {
            throw new LogicException('Unable to resume subscription that is not within grace period.');
        }

        $result = $this->client()->subscriptions->update($subscription->platform_subscription_id, [
            'cancel_at_period_end' => false,
            'trial_end' => $subscription->onTrial() ? $subscription->trial_ends_at->getTimestamp() : 'now',
        ]);

        return (new SubscriptionInfo)
            ->setRaw($result->toArray())
            ->setStatus(Util::mapStatus($result->status));
    }

    protected function asStripeSubscription(Subscription $subscription): StripeSubscription
    {
        return $this->client()->subscriptions->retrieve($subscription->platform_subscription_id);
    }

    protected function mergeItemsThatShouldBeDeletedDuringSwap(StripeSubscription $subscription, Collection $items): Collection
    {
        foreach ($subscription->items->data as $stripeSubscriptionItem) {
            $price = $stripeSubscriptionItem->price;

            if (! $item = $items->get($price->id, [])) {
                $item['deleted'] = true;
            }

            $items->put($price->id, $item + ['id' => $stripeSubscriptionItem->id]);
        }

        return $items;
    }

    protected function parseSwapPrices(array $prices)
    {
        return Collection::make($prices)->mapWithKeys(function ($options, $price) {
            $price = is_string($options) ? $options : $price;

            $options = is_string($options) ? [] : $options;

            $payload = [];

            if (! isset($options['price_data'])) {
                $payload['price'] = $price;
            }

            return [$price => array_merge($payload, $options)];
        });
    }
}
