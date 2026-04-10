<?php

namespace Inovector\MixpostEnterprise;

use Inovector\MixpostEnterprise\Models\Workspace;

class SubscriptionBuilder
{
    protected Workspace $workspace;

    protected string $name;

    protected string $planId;

    protected int $quantity = 1;

    protected ?int $trialDays = null;

    protected bool $skipTrial = false;

    protected ?string $coupon = null;

    protected array $metadata = [];

    protected ?string $returnTo = null;

    protected ?string $cancelUrl = null;

    public function __construct(Workspace $workspace, string $name, string $planId)
    {
        $this->workspace = $workspace;
        $this->name = $name;
        $this->planId = $planId;
    }

    public function quantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function trialDays(int $trialDays): static
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    public function skipTrial(): static
    {
        $this->skipTrial = true;

        return $this;
    }

    public function useCoupon(string $coupon): static
    {
        $this->coupon = $coupon;

        return $this;
    }

    public function withMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function returnTo(string $returnTo): static
    {
        $this->returnTo = $returnTo;

        return $this;
    }

    public function cancelUrl(string $url): static
    {
        $this->cancelUrl = $url;

        return $this;
    }

    public function create()
    {
        $platformInstance = PaymentPlatform::activePlatformInstance();

        // TODO: pass parameter SubscriptionBuilder, example: $platformInstance->createSubscription($this);
        // TODO: refactor Paddle, Stripe, etc. to use SubscriptionBuilder
        return $platformInstance->createSubscription($this->workspace, $this->planId, $this->buildPayload());
    }

    // TODO: remove this method when passing parameter SubscriptionBuilder inside create method
    protected function buildPayload(): array
    {
        return [
            'coupon_code' => (string) $this->coupon,
            'quantity' => $this->quantity,
            'return_url' => $this->returnTo,
            'cancel_url' => $this->cancelUrl,
            'trial_days' => $this->getTrialEndForPayload(),
            'meta_data' => $this->metadata,
            'passthrough' => [
                'subscription_name' => $this->name,
                'billable_id' => $this->workspace->uuid,
            ],
        ];
    }

    protected function getTrialEndForPayload(): ?int
    {
        if ($this->skipTrial) {
            return 0;
        }

        return $this->trialDays;
    }
}
