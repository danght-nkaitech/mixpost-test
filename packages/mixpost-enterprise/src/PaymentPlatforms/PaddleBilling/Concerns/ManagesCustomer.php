<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns;

use Inovector\Mixpost\Facades\Settings;
use Inovector\MixpostEnterprise\Models\Customer;
use Inovector\MixpostEnterprise\Models\Workspace;
use LogicException;

trait ManagesCustomer
{
    public function createAsCustomer(Workspace $workspace, array $options = []): Customer
    {
        $billable = $workspace->owner;

        if ($customer = Customer::where('user_id', $billable->id)->first()) {
            return $customer;
        }

        $options = [
            'locale' => Settings::get('locale', $billable->id) ?? 'en',
        ];

        if (! array_key_exists('name', $options) && $name = $billable->name) {
            $options['name'] = $name;
        }

        if (! array_key_exists('email', $options) && $email = $billable->email) {
            $options['email'] = $email;
        }

        // Attempt to find the customer by email address first...
        $response = $this->makeApiCall('GET', '/customers', [
            'status' => 'active,archived',
            'search' => $options['email'],
        ])['data'][0] ?? null;

        // If we can't find the customer by email, we'll create them on Paddle Billing...
        if (is_null($response)) {
            $response = $this->makeApiCall('POST', '/customers', $options)['data'];
        }

        if (Customer::where('platform_customer_id', $response['id'])->exists()) {
            throw new LogicException("The Paddle Billing customer [{$response['id']}] already exists in the database.");
        }

        return Customer::create([
            'user_id' => $billable->id,
            'platform_customer_id' => $response['id'],
        ]);
    }
}
