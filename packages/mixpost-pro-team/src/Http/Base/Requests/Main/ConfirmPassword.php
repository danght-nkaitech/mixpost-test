<?php

namespace Inovector\Mixpost\Http\Base\Requests\Main;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\ConfirmsPasswords;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Rules\CheckUserPasswordRule;

class ConfirmPassword extends FormRequest
{
    use ConfirmsPasswords;
    use UsesUserModel;

    public function rules(): array
    {
        $user = self::getUserClass()::findOrFail(Auth::id());

        return [
            'password' => [
                'required',
                new CheckUserPasswordRule($user, __('mixpost::auth.password_dont_match')),
            ],
        ];
    }

    public function handle(): void
    {
        $this->confirmPassword();
    }
}
