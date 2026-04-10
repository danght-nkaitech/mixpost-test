<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Exceptions\InvalidPaymentMethod;
use JsonSerializable;
use ReturnTypeWillChange;
use Stripe\PaymentMethod as StripePaymentMethod;

class PaymentMethod implements Arrayable, Jsonable, JsonSerializable
{
    public function __construct(public readonly Billable $billable, public readonly StripePaymentMethod $paymentMethod)
    {
        if ($this->billable->workspace->stripe_id !== $paymentMethod->customer) {
            throw InvalidPaymentMethod::invalidWorkspace($paymentMethod, $this->billable->workspace);
        }
    }

    public function delete(): void
    {
        $this->billable->deletePaymentMethod($this->paymentMethod);
    }

    public function workspace(): Workspace
    {
        return $this->billable->workspace;
    }

    public function asStripePaymentMethod(): StripePaymentMethod
    {
        return $this->paymentMethod;
    }

    public function toArray(): array
    {
        return $this->asStripePaymentMethod()->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __get($key)
    {
        return $this->paymentMethod->{$key};
    }
}
