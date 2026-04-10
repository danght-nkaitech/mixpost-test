<?php

namespace Inovector\MixpostEnterprise\Configs;

use Inovector\Mixpost\Abstracts\Config;

class OnboardingConfig extends Config
{
    public function group(): string
    {
        return 'onboarding';
    }

    public function form(): array
    {
        return [
            'allow_register' => true,
            'email_verification' => true,
            'delete_unverified_users' => true,
            'allow_account_deletion' => true,
            'register_title' => __('mixpost-enterprise::panel.register_account'),
            'register_description' => __('mixpost-enterprise::panel.start_free_trial'),
            'terms_accept_description' => __('mixpost-enterprise::panel.registering_accepting').'<a href="/terms" target="_blank">'.__('mixpost-enterprise::panel.terms_use').'</a>',
        ];
    }

    public function rules(): array
    {
        return [
            'allow_register' => ['required', 'boolean'],
            'email_verification' => ['required', 'boolean'],
            'register_title' => ['sometimes', 'nullable', 'max:255'],
            'register_description' => ['sometimes', 'nullable', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function allowRegister(): bool
    {
        return $this->get('allow_register');
    }
}
