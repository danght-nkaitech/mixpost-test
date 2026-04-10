<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\SubscriptionInfo;
use Inovector\MixpostEnterprise\SubscriptionPayment;

trait ManagesSubscriptions
{
    use UsesAuth;

    public function createSubscription(Workspace $workspace, string $planId, array $payload)
    {
        $data = [
            'product_id' => $planId,
            'passthrough' => json_encode($payload['passthrough']),
        ];

        if ($couponCode = Arr::get($payload, 'coupon_code')) {
            $data['coupon_code'] = $couponCode;
        }

        if ($returnUrl = Arr::get($payload, 'return_url')) {
            $data['return_url'] = $returnUrl;
        }

        if (! is_null($payload['trial_days'])) {
            $currency = Arr::get($payload, 'meta_data.currency', 'USD');

            $data['trial_days'] = $payload['trial_days'];
            $data['prices'] = ["$currency:0.00"];
        }

        $payLink = $this->makeApiCall('post', '/product/generate_pay_link', $data)['response']['url'];

        return view('mixpost-enterprise::payment_platforms.paddle', [
            'payLink' => $payLink,
            'sandbox' => filter_var(
                Arr::get($this->options, 'sandbox', false), FILTER_VALIDATE_BOOLEAN
            ),
            'vendor' => $this->credentials['vendor_id'] ?? '',
            'email' => self::getAuthGuard()->user()->email,
        ])->render();
    }

    public function subscriptionInfo(Subscription $subscription): SubscriptionInfo
    {
        $request = $this->makeApiCall('post', '/subscription/users', [
            'subscription_id' => $subscription->platform_subscription_id,
        ]);

        $response = $request['response'][0];

        $info = (new SubscriptionInfo)
            ->setRaw($response)
            ->setStatus($this->mapStatus($response['state']))
            ->setPaymentMethod($response['payment_information']['payment_method'] ?? '')
            ->setCardBrand($response['payment_information']['card_type'] ?? '')
            ->setCardLastFourDigits((string) $response['payment_information']['last_four_digits'] ?? '')
            ->setCardExpirationDate((string) $response['payment_information']['expiry_date'] ?? '')
            ->setPortalUrl($response['update_url'])
            ->setLastPayment(new SubscriptionPayment(
                $response['last_payment']['amount'],
                $response['last_payment']['currency'],
                Carbon::createFromFormat('Y-m-d', $response['last_payment']['date'], 'UTC')->startOfDay()
            ));

        if (isset($response['next_payment'])) {
            $info->setNextPayment(new SubscriptionPayment(
                $response['next_payment']['amount'],
                $response['next_payment']['currency'],
                Carbon::createFromFormat('Y-m-d', $response['next_payment']['date'], 'UTC')->startOfDay()
            ));
        }

        return $info;
    }

    public function swapSubscription(Subscription $subscription, string $newPlanId, array $payload): SubscriptionInfo
    {
        $result = $this->makeApiCall('post', '/subscription/users/update', [
            'subscription_id' => $subscription->platform_subscription_id,
            'plan_id' => $newPlanId,
            'prorate' => $payload['prorate'] ?? true,
            'bill_immediately' => $payload['bill_immediately'] ?? true,
        ]);

        $response = $result['response'][0];

        return (new SubscriptionInfo)
            ->setRaw($response)
            ->setStatus($this->mapStatus($response['state']));
    }

    public function cancelSubscription(Subscription $subscription, Carbon $endsAt): SubscriptionInfo
    {
        $result = $this->makeApiCall('post', '/subscription/users_cancel', [
            'subscription_id' => $subscription->platform_subscription_id,
        ]);

        $response = $result['response'][0];

        return (new SubscriptionInfo)
            ->setRaw($response)
            ->setStatus($this->mapStatus($response['state']));
    }
}
