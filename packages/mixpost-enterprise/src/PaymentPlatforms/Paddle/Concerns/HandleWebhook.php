<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCanceled;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCreated;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionPaymentFailed;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionPaymentSucceeded;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionUpdated;
use Inovector\MixpostEnterprise\Exceptions\InvalidPassthroughPayload;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Response;

trait HandleWebhook
{
    public function verifyWebhookSignature(Request $request): bool
    {
        return VerifyWebhookSignature::handle($request, $this->credentials['public_key']);
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = $request->all();

        if (! isset($payload['alert_name'])) {
            return new Response;
        }

        $method = 'handle'.Str::studly($payload['alert_name']);

        if (method_exists($this, $method)) {
            try {
                $this->{$method}($payload);
            } catch (InvalidPassthroughPayload $e) {
                return new Response('Webhook Skipped');
            }

            return new Response('Webhook Handled');
        }

        return new Response;
    }

    protected function handleSubscriptionPaymentSucceeded(array $payload): void
    {
        if ($this->receiptExists($payload['order_id'])) {
            return;
        }

        if ($subscription = $this->findSubscription($payload['subscription_id'])) {
            $billable = $subscription->workspace;
        } else {
            $billable = $this->findBillable($payload['passthrough']);
        }

        $receipt = $billable->receipts()->create([
            'transaction_id' => $payload['checkout_id'],
            'invoice_number' => $payload['order_id'],
            'platform_plan_id' => $payload['subscription_plan_id'],
            'amount' => $payload['sale_gross'],
            'tax' => $payload['payment_tax'],
            'currency' => $payload['currency'],
            'quantity' => (int) $payload['quantity'],
            'receipt_url' => $payload['receipt_url'],
            'description' => $payload['plan_name'],
            'paid_at' => Carbon::createFromFormat('Y-m-d H:i:s', $payload['event_time'], 'UTC'),
        ]);

        SubscriptionPaymentSucceeded::dispatch($receipt->setRelation('workspace', $billable->workspace), $payload);
    }

    protected function handleSubscriptionPaymentFailed(array $payload): void
    {
        if ($billable = $this->findBillable($payload['passthrough'])) {
            SubscriptionPaymentFailed::dispatch($billable, $payload);
        }
    }

    protected function handleSubscriptionCreated(array $payload): void
    {
        $passthrough = isset($payload['passthrough']) ? json_decode($payload['passthrough'], true) : null;

        if (! isset($passthrough) || ! is_array($passthrough) || ! isset($passthrough['subscription_name'])) {
            throw new InvalidPassthroughPayload;
        }

        $billable = $this->findBillable($payload['passthrough']);

        $trialEndsAt = $payload['status'] === 'trialing'
            ? Carbon::createFromFormat('Y-m-d', $payload['next_bill_date'], 'UTC')->startOfDay()
            : null;

        // TODO: Check if we need to delete all subscriptions
        $billable->subscriptions()->delete();

        $subscription = $billable->subscriptions()->create([
            'name' => $passthrough['subscription_name'],
            'platform_subscription_id' => $payload['subscription_id'],
            'platform_plan_id' => $payload['subscription_plan_id'],
            'status' => $this->mapStatus($payload['status']),
            'quantity' => $payload['quantity'],
            'trial_ends_at' => $trialEndsAt,
        ]);

        $subscriptionInfo = $this->subscriptionInfo($subscription);

        $billable->savePaymentMethod(
            $subscriptionInfo->paymentMethod,
            $subscriptionInfo->cardBrand,
            $subscriptionInfo->cardLastFourDigits,
            $subscriptionInfo->cardExpirationDate
        );

        SubscriptionCreated::dispatch($subscription->setRelation('workspace', $billable), $payload);
    }

    protected function handleSubscriptionUpdated(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['subscription_id'])) {
            return;
        }

        // Plan...
        if (isset($payload['subscription_plan_id'])) {
            $subscription->platform_plan_id = $payload['subscription_plan_id'];
        }

        // Status...
        if (isset($payload['status'])) {
            $subscription->status = $this->mapStatus($payload['status']);
        }

        // Paused...
        if (isset($payload['paused_from'])) {
            $subscription->paused_from = Carbon::createFromFormat('Y-m-d H:i:s', $payload['paused_from'], 'UTC');
        } else {
            $subscription->paused_from = null;
        }

        $subscription->save();

        SubscriptionUpdated::dispatch($subscription, $payload);
    }

    protected function handleSubscriptionCancelled(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['subscription_id'])) {
            return;
        }

        // Cancellation date...
        if (is_null($subscription->ends_at)) {
            $subscription->ends_at = $subscription->onTrial()
                ? $subscription->trial_ends_at
                : Carbon::createFromFormat('Y-m-d', $payload['cancellation_effective_date'], 'UTC')->startOfDay();
        }

        // Status...
        if (isset($payload['status'])) {
            $subscription->status = $this->mapStatus($payload['status']);
        }

        $subscription->paused_from = null;

        $subscription->save();

        SubscriptionCanceled::dispatch($subscription, $payload);
    }

    protected function findBillable(string $passthrough): ?Workspace
    {
        $passthrough = json_decode($passthrough, true);

        if (! is_array($passthrough) || ! isset($passthrough['billable_id'])) {
            throw new InvalidPassthroughPayload;
        }

        return $this->findWorkspace($passthrough['billable_id']);
    }

    public function mapStatus(string $paddleStatus): SubscriptionStatus
    {
        return match ($paddleStatus) {
            'active' => SubscriptionStatus::ACTIVE,
            'trialing' => SubscriptionStatus::TRIALING,
            'past_due' => SubscriptionStatus::PAST_DUE,
            'paused' => SubscriptionStatus::PAUSED,
            'deleted' => SubscriptionStatus::CANCELED,
        };
    }
}
