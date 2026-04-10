<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns;

use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\PaymentMethod;
use Stripe\Account as StripeAccount;
use Stripe\BankAccount as StripeBankAccount;
use Stripe\Card as StripeCard;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Source;

trait ManagesPaymentMethods
{
    public function createSetupIntent(array $options = [])
    {
        if ($this->workspaceHasStripeId()) {
            $options['customer'] = $this->workspace->stripe_id;
        }

        return static::client()->setupIntents->create($options);
    }

    public function hasDefaultPaymentMethod(): bool
    {
        return (bool) $this->workspace->pm_type;
    }

    public function addPaymentMethod($paymentMethod)
    {
        $this->assertCustomerExists();

        $stripePaymentMethod = $this->resolveStripePaymentMethod($paymentMethod);

        if ($stripePaymentMethod->customer !== $this->workspace->stripe_id) {
            $stripePaymentMethod = $stripePaymentMethod->attach(
                ['customer' => $this->workspace->stripe_id]
            );
        }

        return $stripePaymentMethod;
    }

    public function deletePaymentMethod($paymentMethod): void
    {
        $this->assertCustomerExists();

        $stripePaymentMethod = $this->resolveStripePaymentMethod($paymentMethod);

        if ($stripePaymentMethod->customer !== $this->workspace->stripe_id) {
            return;
        }

        $customer = $this->getCustomer();

        $defaultPaymentMethod = $customer->invoice_settings->default_payment_method;

        $stripePaymentMethod->detach();

        // If the payment method was the default payment method, we'll remove it manually...
        if ($stripePaymentMethod->id === $defaultPaymentMethod) {
            $this->workspace->removePaymentMethod();
        }
    }

    public function defaultPaymentMethod(): StripeAccount|StripeBankAccount|string|Source|PaymentMethod|StripeCard|null
    {
        if (! $this->workspaceHasStripeId()) {
            return null;
        }

        $customer = $this->getCustomer(['default_source', 'invoice_settings.default_payment_method']);

        if ($customer->invoice_settings->default_payment_method) {
            return new PaymentMethod($this, $customer->invoice_settings->default_payment_method);
        }

        // If we can't find a payment method, try to return a legacy source...
        return $customer->default_source;
    }

    public function updateDefaultPaymentMethod(PaymentMethod|string $paymentMethod)
    {
        $this->assertCustomerExists();

        $customer = $this->getCustomer();

        $stripePaymentMethod = $this->resolveStripePaymentMethod($paymentMethod);

        if ($stripePaymentMethod->id === $customer->invoice_settings->default_payment_method) {
            return $stripePaymentMethod;
        }

        $paymentMethod = $this->addPaymentMethod($stripePaymentMethod);

        $this->updateCustomer([
            'invoice_settings' => ['default_payment_method' => $paymentMethod->id],
        ]);

        $this->savePaymentMethodDetails($paymentMethod);

        return $paymentMethod;
    }

    public function updateDefaultPaymentMethodFromStripe(): void
    {
        $defaultPaymentMethod = $this->defaultPaymentMethod();

        if ($defaultPaymentMethod) {
            if ($defaultPaymentMethod instanceof PaymentMethod) {
                $this->savePaymentMethodDetails($defaultPaymentMethod->asStripePaymentMethod());

                return;
            }

            $this->saveSourceDetails($defaultPaymentMethod);

            return;
        }

        $this->workspace->removePaymentMethod();
    }

    protected function savePaymentMethodDetails($paymentMethod): void
    {
        if ($paymentMethod->type === 'card') {
            $this->workspace->savePaymentMethod(
                'card',
                $paymentMethod->card->brand,
                $paymentMethod->card->last4,
                $paymentMethod->card->exp_month.'/'.$paymentMethod->card->exp_year
            );

            return;
        }

        $type = $paymentMethod->type;

        $this->workspace->savePaymentMethod(
            $type,
            null,
            $paymentMethod?->$type->last4 ?? null,
            null
        );
    }

    protected function saveSourceDetails($source): void
    {
        if ($source instanceof StripeCard) {
            $this->workspace->savePaymentMethod(
                'card',
                $source->brand,
                $source->last4,
                $source->exp_month.'/'.$source->exp_year
            );
        } elseif ($source instanceof StripeBankAccount) {
            $this->workspace->savePaymentMethod(
                'Bank Account',
                null,
                $source->last4,
                null
            );
        }
    }

    protected function resolveStripePaymentMethod($paymentMethod)
    {
        if ($paymentMethod instanceof StripePaymentMethod) {
            return $paymentMethod;
        }

        return static::client()->paymentMethods->retrieve($paymentMethod);
    }
}
