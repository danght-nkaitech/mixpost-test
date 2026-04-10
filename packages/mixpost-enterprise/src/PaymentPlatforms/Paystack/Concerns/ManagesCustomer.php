<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns;

use Exception;

trait ManagesCustomer
{
    public function createCustomer(array $options = [])
    {
        if (! array_key_exists('email', $options) && $email = $this->workspace->owner?->email) {
            $options['email'] = $email;
        }

        if (! array_key_exists('metadata', $options) && $metadata = $this->metadata()) {
            $options['metadata'] = $metadata;
        }

        $result = static::makeApiCall('post', '/customer', $options);

        if ($result->successful()) {
            return $result->json('data');
        }

        throw new Exception($result->json('message'));
    }

    public function getCustomer($options = [])
    {
        $result = static::makeApiCall('get', '/customer/'.$this->workspace->owner->email, $options);

        if (! $result->successful()) {
            return null;
        }

        return $result->json('data');
    }

    public function updateCustomer(string $customerId, array $options = [])
    {
        if (! array_key_exists('email', $options) && $email = $this->workspace->owner?->email) {
            $options['email'] = $email;
        }

        if (! array_key_exists('metadata', $options) && $metadata = $this->metadata()) {
            $options['metadata'] = $metadata;
        }

        $result = static::makeApiCall('put', '/customer/'.$customerId, $options);

        if ($result->successful()) {
            return $result->json('data');
        }

        throw new Exception($result->json('message'));
    }

    /**
     * @throws Exception
     */
    public function updateOrCreateCustomer(array $options = [])
    {
        if ($customer = $this->getCustomer($options)) {
            return array_merge(
                ['authorizations' => $customer['authorizations']],
                $this->updateCustomer($customer['customer_code'], $options)
            );
        }

        return $this->createCustomer($options);
    }

    public function metadata(): array
    {
        return [
            'workspace_uuid' => $this->workspace->uuid,
        ];
    }
}
