<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns;

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
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Response;

trait HandleWebhook
{
    public function verifyWebhookSignature(Request $request): bool
    {
        return VerifyWebhookSignature::handle($request, $this->credentials['webhook_secret']);
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = $request->all();

        $method = 'handle'.Str::studly(Str::replace('.', ' ', $payload['event_type']));

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

    protected function handleTransactionCompleted(array $payload): void
    {
        $data = $payload['data'];

        if (! $data['invoice_number']) {
            return;
        }

        $billable = $this->findBillable($data);

        if (! $billable) {
            return;
        }

        if ($billable->receipts()->where('invoice_number', $data['invoice_number'])->exists()) {
            return;
        }

        $receipt = $billable->receipts()->create([
            'transaction_id' => $data['id'],
            'invoice_number' => $data['invoice_number'],
            'platform_plan_id' => $data['items'][0]['price_id'],
            'amount' => $data['details']['totals']['total'] / 100,
            'tax' => $data['details']['totals']['tax'] / 100,
            'currency' => $data['currency_code'],
            'quantity' => 1,
            'description' => $data['details']['line_items'][0]['product']['name'].' - '.$data['items'][0]['price']['name'],
            'paid_at' => Carbon::parse($data['billed_at'], 'UTC'),
        ]);

        SubscriptionPaymentSucceeded::dispatch($receipt->setRelation('workspace', $billable->workspace), $payload);
    }

    protected function handleTransactionUpdated(array $payload): void
    {
        $data = $payload['data'];

        $billable = $this->findBillable($data);

        if (! $billable) {
            return;
        }

        if (! $receipt = $billable->receipts()->where('transaction_id', $data['id'])->first()) {
            return;
        }

        $receipt->update([
            'invoice_number' => $data['invoice_number'] ?? $receipt->invoice_number,
            'total' => $data['details']['totals']['total'] / 100,
            'tax' => $data['details']['totals']['tax'] / 100,
            'paid_at' => Carbon::parse($data['billed_at'], 'UTC'),
        ]);
    }

    protected function handleTransactionPaymentFailed(array $payload): void
    {
        $data = $payload['data'];

        $billable = $this->findBillable($data);

        if (! $billable) {
            return;
        }

        SubscriptionPaymentFailed::dispatch($billable, $payload);
    }

    protected function handleSubscriptionCreated(array $payload): void
    {
        $data = $payload['data'];

        $billable = $this->findBillable($data);

        if (! $billable) {
            return;
        }

        if ($billable->subscriptions()->where('platform_subscription_id', $data['id'])->exists()) {
            return;
        }

        if (empty($data['items'])) {
            return;
        }

        $trialEndsAt = $data['status'] === 'trialing'
            ? Carbon::parse($data['next_billed_at'], 'UTC')->startOfDay()
            : null;

        $billable->subscriptions()->delete(); // Clean up old subscriptions

        $subscription = $billable->subscriptions()->create([
            'name' => $data['custom_data']['subscription_type'] ?? Subscription::DEFAULT_NAME,
            'platform_subscription_id' => $data['id'],
            'platform_plan_id' => $data['items'][0]['price']['id'],
            'status' => $this->mapStatus($data['status']),
            'quantity' => 1,
            'trial_ends_at' => $trialEndsAt,
        ]);

        SubscriptionCreated::dispatch($subscription->setRelation('workspace', $billable), $payload);
    }

    protected function handleSubscriptionUpdated(array $payload): void
    {
        $data = $payload['data'];

        if (! $subscription = $this->findSubscription($data['id'])) {
            return;
        }

        $subscription->status = $this->mapStatus($data['status']);

        if ($data['status'] === SubscriptionStatus::TRIALING) {
            $subscription->trial_ends_at = Carbon::parse($data['next_billed_at'], 'UTC');
        } else {
            $subscription->trial_ends_at = null;
        }

        if (isset($data['paused_at'])) {
            $subscription->paused_from = Carbon::parse($data['paused_at'], 'UTC');
        } elseif (isset($data['scheduled_change']) && $data['scheduled_change']['action'] === 'pause') {
            $subscription->paused_from = Carbon::parse($data['scheduled_change']['effective_at'], 'UTC');
        } else {
            $subscription->paused_from = null;
        }

        if (isset($data['canceled_at'])) {
            $subscription->status = SubscriptionStatus::CANCELED;
            $subscription->ends_at = Carbon::parse($data['canceled_at'], 'UTC');
        } elseif (isset($data['scheduled_change']) && $data['scheduled_change']['action'] === 'cancel') {
            $subscription->status = SubscriptionStatus::CANCELED;
            $subscription->ends_at = Carbon::parse($data['scheduled_change']['effective_at'], 'UTC');
        } else {
            $subscription->ends_at = null;
        }

        if (empty($data['items'])) {
            $subscription->platform_plan_id = $data['items'][0]['price']['id'];
        }

        $subscription->save();

        SubscriptionUpdated::dispatch($subscription, $payload);
    }

    protected function handleSubscriptionPaused(array $payload): void
    {
        $data = $payload['data'];

        if (! $subscription = $this->findSubscription($data['id'])) {
            return;
        }

        $subscription->status = SubscriptionStatus::PAUSED;

        $subscription->paused_from = Carbon::parse($data['paused_at'], 'UTC');

        $subscription->ends_at = null;

        $subscription->save();
    }

    protected function handleSubscriptionCanceled(array $payload): void
    {
        $data = $payload['data'];

        if (! $subscription = $this->findSubscription($data['id'])) {
            return;
        }

        $subscription->status = SubscriptionStatus::CANCELED;

        $subscription->ends_at = Carbon::parse($data['canceled_at'], 'UTC');

        $subscription->paused_from = null;

        $subscription->save();

        SubscriptionCanceled::dispatch($subscription, $payload);
    }

    protected function findBillable(array $data): ?Workspace
    {
        return Workspace::findByUuid($data['custom_data']['workspace_uuid'] ?? '');
    }

    public function mapStatus(string $paddleStatus): SubscriptionStatus
    {
        return match ($paddleStatus) {
            'active' => SubscriptionStatus::ACTIVE,
            'trialing' => SubscriptionStatus::TRIALING,
            'past_due' => SubscriptionStatus::PAST_DUE,
            'paused' => SubscriptionStatus::PAUSED,
            'canceled' => SubscriptionStatus::CANCELED,
        };
    }
}
