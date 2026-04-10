<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCanceled;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCreated;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionPaymentSucceeded;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionUpdated;
use Inovector\MixpostEnterprise\Exceptions\InvalidPassthroughPayload;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Billable;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Util;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\VerifyWebhookSignature;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Symfony\Component\HttpFoundation\Response;

trait HandleWebhook
{
    public function verifyWebhookSignature(Request $request): bool
    {
        return VerifyWebhookSignature::handle($request, $this->credentials['webhook_secret']);
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.Str::studly(str_replace('.', '_', $payload['type']));

        if (method_exists($this, $method)) {
            $this->setMaxNetworkRetries();

            try {
                $this->{$method}($payload);
            } catch (InvalidPassthroughPayload $e) {
                return new Response('Webhook Skipped');
            }

            return new Response('Webhook Handled');
        }

        return new Response;
    }

    protected function handleInvoicePaymentSucceeded(array $payload): void
    {
        if (! $billable = $this->findBillable($payload)) {
            return;
        }

        $data = $payload['data']['object'];

        if ($billable->workspace->receipts()->where('invoice_number', $data['number'])->exists()) {
            return;
        }

        $receipt = $billable->workspace->receipts()->create([
            'transaction_id' => $data['id'],
            'invoice_number' => $data['number'],
            'platform_plan_id' => $data['lines']['data'][0]['price']['id'],
            'amount' => number_format($data['amount_paid'] / 100, 2),
            'tax' => $data['tax'] ? number_format($data['tax'] / 100, 2) : 0,
            'currency' => Str::upper($data['currency']),
            'quantity' => (int) $data['lines']['data'][0]['quantity'],
            'receipt_url' => '',
            'description' => $data['lines']['data'][0]['description'],
            'paid_at' => Carbon::createFromTimestamp($data['status_transitions']['paid_at']),
        ]);

        SubscriptionPaymentSucceeded::dispatch($receipt->setRelation('workspace', $billable->workspace), $payload);
    }

    protected function handleCustomerSubscriptionCreated(array $payload): void
    {
        if (! $billable = $this->findBillable($payload)) {
            return;
        }

        $data = $payload['data']['object'];

        if ($this->findBillableSubscription($billable, $data['id'])) {
            return;
        }

        $firstItem = $data['items']['data'][0];

        $subscription = $billable->workspace->subscriptions()->create([
            'name' => $data['metadata']['name'] ?? 'default',
            'platform_subscription_id' => $data['id'],
            'platform_plan_id' => $firstItem['price']['id'],
            'status' => Util::mapStatus($data['status']),
            'quantity' => $firstItem['quantity'],
            'trial_ends_at' => isset($data['trial_end']) ? Carbon::createFromTimestamp($data['trial_end']) : null,
        ]);

        SubscriptionCreated::dispatch($subscription->setRelation('workspace', $billable->workspace), $payload);
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): void
    {
        if (! $billable = $this->findBillable($payload)) {
            return;
        }

        $data = $payload['data']['object'];

        if (! $subscription = $this->findBillableSubscription($billable, $data['id'])) {
            return;
        }

        if (
            isset($data['status']) &&
            $data['status'] === StripeSubscription::STATUS_INCOMPLETE_EXPIRED
        ) {
            $subscription->delete();

            return;
        }

        $firstItem = $data['items']['data'][0];

        // Status...
        if (isset($data['status'])) {
            $subscription->status = Util::mapStatus($data['status']);
        }

        // Price...
        $subscription->platform_plan_id = $firstItem['price']['id'];

        // Trial ending date...
        if (isset($data['trial_end'])) {
            $trialEnd = Carbon::createFromTimestamp($data['trial_end']);

            if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trialEnd)) {
                $subscription->trial_ends_at = $trialEnd;
            }
        }

        // Cancellation date...
        if (isset($data['cancel_at_period_end'])) {
            if ($data['cancel_at_period_end']) {
                $subscription->status = SubscriptionStatus::CANCELED;

                $subscription->ends_at = $subscription->onTrial()
                    ? $subscription->trial_ends_at
                    : Carbon::createFromTimestamp($data['current_period_end']);
            } elseif (isset($data['cancel_at'])) {
                $subscription->status = SubscriptionStatus::CANCELED;

                $subscription->ends_at = Carbon::createFromTimestamp($data['cancel_at']);
            } else {
                $subscription->ends_at = null;
            }
        }

        $subscription->save();

        SubscriptionUpdated::dispatch($subscription->fresh()->setRelation('workspace', $billable->workspace), $payload);
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): void
    {
        if (! $billable = $this->findBillable($payload)) {
            return;
        }

        $data = $payload['data']['object'];

        if (! $subscription = $this->findBillableSubscription($billable, $data['id'])) {
            return;
        }

        $subscription->ends_at = $subscription->onTrial()
            ? $subscription->trial_ends_at
            : Carbon::now()->startOfDay();

        $subscription->paused_from = null;

        $subscription->status = SubscriptionStatus::CANCELED;

        $subscription->save();

        SubscriptionCanceled::dispatch($subscription->fresh()->setRelation('workspace', $billable->workspace), $payload);
    }

    protected function handleCustomerUpdated(array $payload): void
    {
        if (! $billable = $this->findBillable($payload)) {
            return;
        }

        $billable->updateDefaultPaymentMethodFromStripe();
    }

    protected function handleCustomerDeleted(array $payload): void
    {
        if (! $billable = $this->findBillable($payload)) {
            return;
        }

        $billable->workspace->forceFill([
            'stripe_id' => null,
        ])->save();

        $billable->workspace->removePaymentMethod();

        $billable->workspace->subscriptions->delete();
    }

    protected function handlePaymentMethodAutomaticallyUpdated(array $payload): void
    {
        if (! $billable = $this->findBillable($payload)) {
            return;
        }

        $billable->updateDefaultPaymentMethodFromStripe();
    }

    protected function findBillable(array $payload): ?Billable
    {
        $customer = Arr::get($payload, 'data.object.object') === 'customer' ?
            $payload['data']['object']['id'] :
            $payload['data']['object']['customer'];

        $workspace = Workspace::where('stripe_id', $customer)->first();

        if (! $workspace) {
            return null;
        }

        return new Billable($this->credentials, $workspace);
    }

    protected function findBillableSubscription(Billable $billable, string $id): ?Subscription
    {
        return $billable->workspace->subscriptions->where('platform_subscription_id', $id)->first();
    }

    protected function setMaxNetworkRetries($retries = 3): void
    {
        Stripe::setMaxNetworkRetries($retries);
    }
}
