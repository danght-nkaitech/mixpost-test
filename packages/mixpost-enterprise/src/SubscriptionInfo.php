<?php

namespace Inovector\MixpostEnterprise;

use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;

class SubscriptionInfo
{
    public ?SubscriptionStatus $status = null;

    public array $raw = [];

    public string $email = '';

    public string $paymentMethod = '';

    public string $cardBrand = '';

    public string $cardLastFourDigits = '';

    public string $cardExpirationDate = '';

    public string $portalUrl = '';

    public ?SubscriptionPayment $lastPayment = null;

    public ?SubscriptionPayment $nextPayment = null;

    public function setRaw(array $value): static
    {
        $this->raw = $value;

        return $this;
    }

    public function setStatus(SubscriptionStatus $value): static
    {
        $this->status = $value;

        return $this;
    }

    public function setLastPayment(SubscriptionPayment $value): static
    {
        $this->lastPayment = $value;

        return $this;
    }

    public function setNextPayment(SubscriptionPayment $value): static
    {
        $this->nextPayment = $value;

        return $this;
    }

    public function setEmail(string $value): static
    {
        $this->email = $value;

        return $this;
    }

    public function setPaymentMethod(string $value): static
    {
        $this->paymentMethod = $value;

        return $this;
    }

    public function setCardBrand(string $value): static
    {
        $this->cardBrand = $value;

        return $this;
    }

    public function setCardLastFourDigits(string $value): static
    {
        $this->cardLastFourDigits = $value;

        return $this;
    }

    public function setCardExpirationDate(string $value): static
    {
        $this->cardExpirationDate = $value;

        return $this;
    }

    public function setPortalUrl(string $value): static
    {
        $this->portalUrl = $value;

        return $this;
    }
}
