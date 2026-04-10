<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Inovector\MixpostEnterprise\AccountDeletionManager;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;

class DeleteAccount extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) app(OnboardingConfig::class)->get('allow_account_deletion');
    }

    public function rules(): array
    {
        return [];
    }

    public function handle(): void
    {
        app(AccountDeletionManager::class)->deleteAccount(Auth::user());
    }
}
