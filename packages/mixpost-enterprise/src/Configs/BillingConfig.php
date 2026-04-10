<?php

namespace Inovector\MixpostEnterprise\Configs;

use Illuminate\Validation\Rule;
use Inovector\Mixpost\Abstracts\Config;
use Inovector\MixpostEnterprise\Util;

class BillingConfig extends Config
{
    public function group(): string
    {
        return 'billing';
    }

    public function form(): array
    {
        return [
            'currency' => 'USD',
            'billing_cycle' => 'monthly_yearly',
            'trial_days' => '7',
            'generic_trial' => false,
            'prorate' => true,
            'bill_immediately' => true,
            'plans_page_url' => '',
            'plans_page_url_title' => '',
            'receipt_title' => '',
            'company_details' => '',
        ];
    }

    public function rules(): array
    {
        return [
            'currency' => ['required', Rule::in(Util::currencies()->pluck('code'))],
            'billing_cycle' => ['required', Rule::in(['monthly_yearly', 'monthly', 'yearly'])],
            'trial_days' => ['required', 'nullable', 'integer', 'min:0'],
            'generic_trial' => ['required', 'boolean'],
            'prorate' => ['required', 'boolean'],
            'bill_immediately' => ['required', 'boolean'],
            'plans_page_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'plans_page_url_title' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
