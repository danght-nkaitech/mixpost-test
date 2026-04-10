<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns;

use Exception;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Billable;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Util;
use Inovector\MixpostEnterprise\SubscriptionInfo;
use Inovector\MixpostEnterprise\SubscriptionPayment;

trait ManagesSubscriptions
{
    use UsesAuth;

    public function createSubscription(Workspace $workspace, string $planId, array $payload): string
    {
        $this->ensureCurrencySupport($payload['meta_data']['currency']);

        $billable = new Billable($this->credentials, $workspace);

        $customer = $billable->updateOrCreateCustomer();

        if (request('delete_payment_method')) {
            return $this->deletePaymentMethod(request('authorization_code'));
        }

        if (request('add_subscription')) {
            $data = [
                'customer' => $customer['customer_code'],
                'plan' => $planId,
                'start_date' => Carbon::now('UTC')->toIso8601String(),
            ];

            if (request('authorization_code')) {
                $data['authorization'] = request('authorization_code');
            }

            $result = $this->makeApiCall('post', '/subscription', $data);

            if (! $result->successful()) {
                return $result->json('message');
            }

            return 'ok';
        }

        return view('mixpost-enterprise::payment_platforms.paystack', [
            'key' => $this->credentials['public_key'],
            'email' => $customer['email'],
            'plan_code' => $planId,
            'passthrough' => $payload['passthrough'],
            'authorizations' => $customer['authorizations'],
            'create_subscription_url' => route('mixpost_e.workspace.subscription.new', ['workspace' => $billable->workspace->uuid]),
            'success_url' => route('mixpost_e.workspace.billing', ['workspace' => $billable->workspace->uuid, 'delay' => true]),
            'original_request' => [
                'plan_id' => request('plan_id'),
                'cycle' => request('cycle'),
            ],
        ])->render();
    }

    public function subscriptionInfo(Subscription $subscription): SubscriptionInfo
    {
        $paystackSubscriptionResult = $this->makeApiCall('get', '/subscription/'.$subscription->platform_subscription_id);

        if ($paystackSubscriptionResult->successful()) {
            $paystackSubscription = $paystackSubscriptionResult->json('data');
        } else {
            throw new Exception($paystackSubscriptionResult->json('message'));
        }

        $billingPortalUrl = $this->makeApiCall('get', '/subscription/'.$subscription->platform_subscription_id.'/manage/link')->json('data');

        return (new SubscriptionInfo)
            ->setRaw($paystackSubscription)
            ->setStatus(Util::mapStatus($subscription->status->value))
            ->setNextPayment(new SubscriptionPayment(
                $paystackSubscription['amount'] / 100,
                $paystackSubscription['plan']['currency'],
                Util::createDateTime($paystackSubscription['next_payment_date'])
            ))
            ->setPortalUrl($billingPortalUrl['link']);
    }

    public function swapSubscription(Subscription $subscription, string $newPlanId, array $payload): SubscriptionInfo
    {
        // Paystack doesn't support swapping plan subscription
        // User will have to cancel the current subscription and create a new one

        return (new SubscriptionInfo)->setRaw([]);
    }

    public function cancelSubscription(Subscription $subscription, Carbon $endsAt): SubscriptionInfo
    {
        $result = $this->makeApiCall('post', '/subscription/disable', [
            'code' => $subscription->platform_subscription_id,
            'token' => $subscription->platform_data['email_token'],
        ]);

        return (new SubscriptionInfo)
            ->setStatus($result->json('status', false) ? SubscriptionStatus::CANCELED : $subscription->status);
    }

    private function deletePaymentMethod(string $authorizationCode)
    {
        $result = $this->makeApiCall('post', '/customer/deactivate_authorization', [
            'authorization_code' => $authorizationCode,
        ]);

        if (! $result->successful()) {
            return $result->json('message');
        }

        return 'ok';
    }

    private function ensureCurrencySupport(string $currency): void
    {
        if (! in_array($currency, ['NGN', 'GHS', 'ZAR', 'USD'])) {
            throw new Exception('Currency not supported');
        }
    }
}
