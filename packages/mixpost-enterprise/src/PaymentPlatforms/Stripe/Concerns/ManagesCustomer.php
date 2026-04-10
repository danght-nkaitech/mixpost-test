<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns;

use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Exceptions\CustomerAlreadyCreated;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Exceptions\InvalidCustomer;
use Stripe\Customer;

trait ManagesCustomer
{
    public function workspaceHasStripeId(): bool
    {
        return ! is_null($this->workspace->stripe_id);
    }

    public function createCustomer(array $options = []): Customer
    {
        if ($this->workspaceHasStripeId()) {
            throw CustomerAlreadyCreated::exists($this->workspace);
        }

        if (! array_key_exists('name', $options) && $name = $this->workspace->name) {
            $options['name'] = $this->workspace->owner?->name ?? $name;
        }

        if (! array_key_exists('email', $options) && $email = $this->workspace->owner?->email) {
            $options['email'] = $email;
        }

        if (! array_key_exists('metadata', $options) && $metadata = $this->metadata()) {
            $options['metadata'] = $metadata;
        }

        $customer = static::client()->customers->create($options);

        $this->workspace->stripe_id = $customer->id;
        $this->workspace->save();

        return $customer;
    }

    public function getCustomer(array $expand = []): Customer
    {
        $this->assertCustomerExists();

        return static::client()->customers->retrieve($this->workspace->stripe_id, ['expand' => $expand]);
    }

    public function createOrGetCustomer(array $options = []): Customer
    {
        if ($this->workspaceHasStripeId()) {
            return $this->getCustomer($options['expand'] ?? []);
        }

        return $this->createCustomer($options);
    }

    public function updateCustomer(array $options = [])
    {
        return static::client()->customers->update(
            $this->workspace->stripe_id, $options
        );
    }

    public function syncCustomerDetails()
    {
        return $this->updateCustomer([
            'name' => $this->workspace->name,
            'metadata' => $this->metadata(),
        ]);
    }

    public function metadata(): array
    {
        return [
            'workspace_uuid' => $this->workspace->uuid,
        ];
    }

    protected function assertCustomerExists(): void
    {
        if (! $this->workspaceHasStripeId()) {
            throw InvalidCustomer::notYetCreated($this->workspace);
        }
    }
}
