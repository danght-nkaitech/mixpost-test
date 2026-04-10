<?php

namespace Inovector\Mixpost\Http\Base\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Inovector\Mixpost\Concerns\UsesUserModel;

class SendPasswordResetLink extends FormRequest
{
    use UsesUserModel;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function handle(): string
    {
        $appProviderModel = Config::get('auth.providers.users.model');
        Config::set('auth.providers.users.model', self::getUserClass());

        $status = Password::sendResetLink(
            $this->only('email')
        );

        Config::set('auth.providers.users.model', $appProviderModel);

        return $status;
    }
}
