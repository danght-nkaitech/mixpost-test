<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCanceled;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCreated;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionPaymentSucceeded;
use Inovector\MixpostEnterprise\Exceptions\InvalidPassthroughPayload;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Util;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Response;

trait HandleWebhook
{
    public function verifyWebhookSignature(Request $request): bool
    {
        return VerifyWebhookSignature::handle($request, $this->credentials['secret_key']);
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = $request->all();

        if (! isset($payload['event'])) {
            return new Response;
        }

        $method = 'handle'.Str::of($payload['event'])->replace('.', '_')->studly()->toString();

        if (method_exists($this, $method)) {
            try {
                $this->{$method}($payload['data']);
            } catch (InvalidPassthroughPayload $e) {
                return new Response('Webhook Skipped');
            }

            return new Response('Webhook Handled');
        }

        return new Response;
    }

    protected function handleChargeSuccess(array $payload): void
    {
        if ($this->receiptExists($payload['reference'])) {
            return;
        }

        if (Arr::has($payload, 'metadata.custom_fields.0.card_verification')) {
            $this->initiateRefund($payload['reference']);

            return;
        }

        $billable = $this->findBillable($payload);

        $receipt = $billable->receipts()->create([
            'transaction_id' => Str::random(),
            'invoice_number' => $payload['reference'],
            'platform_plan_id' => $payload['plan']['plan_code'] ?? null,
            'amount' => $payload['amount'] / 100,
            'tax' => $payload['fees'] / 100,
            'currency' => $payload['currency'],
            'quantity' => 1,
            'receipt_url' => null,
            'description' => Arr::get($payload, 'metadata.custom_fields.0.description', ($payload['plan']['name'] ?? 'Service Payment')),
            'paid_at' => Util::createDateTime($payload['paidAt']),
        ]);

        SubscriptionPaymentSucceeded::dispatch($receipt->setRelation('workspace', $billable), $payload);
    }

    protected function handleSubscriptionCreate(array $payload): void
    {
        $billable = $this->findBillable($payload);

        if ($billable->subscriptions()->where('platform_subscription_id', $payload['subscription_code'])->exists()) {
            return;
        }

        $subscription = $billable->subscriptions()->create([
            'name' => 'default',
            'platform_subscription_id' => $payload['subscription_code'],
            'platform_plan_id' => $payload['plan']['plan_code'],
            'platform_data' => [
                'email_token' => $payload['email_token'],
            ],
            'status' => Util::mapStatus($payload['status']),
            'quantity' => 1,
        ]);

        $billable->savePaymentMethod(
            type: $payload['authorization']['channel'],
            cardBrand: $payload['authorization']['card_type'] ?? '',
            cardLastFour: $payload['authorization']['last4'] ?? '',
            cardExpires: $payload['authorization']['channel'] === 'card' ? $payload['authorization']['exp_month'].'/'.$payload['authorization']['exp_year'] : null,
        );

        SubscriptionCreated::dispatch($subscription->setRelation('workspace', $billable), $payload);
    }

    protected function handleSubscriptionNotRenew(array $payload): void
    {
        $this->disableSubscription($payload);
    }

    protected function handleSubscriptionDisable(array $payload): void
    {
        $this->disableSubscription($payload);
    }

    protected function disableSubscription(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['subscription_code'])) {
            return;
        }

        $subscription->ends_at = $subscription->onTrial()
            ? $subscription->trial_ends_at
            : $subscription->ends_at ?? Carbon::now()->startOfDay();

        $subscription->paused_from = null;

        $subscription->status = SubscriptionStatus::CANCELED;

        $subscription->save();

        SubscriptionCanceled::dispatch($subscription, $payload);
    }

    protected function initiateRefund(string $reference): void
    {
        $this->makeApiCall('post', '/refund', [
            'transaction' => $reference,
        ]);
    }

    protected function findBillable(array $payload): ?Workspace
    {
        $workspaceId = isset($payload['customer']['metadata']['workspace_uuid']) ? $payload['customer']['metadata']['workspace_uuid'] : null;

        if (! $workspaceId) {
            throw new InvalidPassthroughPayload;
        }

        return $this->findWorkspace($workspaceId);
    }
}
