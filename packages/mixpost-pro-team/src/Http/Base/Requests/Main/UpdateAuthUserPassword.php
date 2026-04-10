<?php

namespace Inovector\Mixpost\Http\Base\Requests\Main;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Rules\CheckUserPasswordRule;

class UpdateAuthUserPassword extends FormRequest
{
    use UsesUserModel;

    protected $user;

    public function rules(): array
    {
        $this->user = self::getUserClass()::findOrFail(Auth::user()->id);

        return [
            'current_password' => ['required', new CheckUserPasswordRule($this->user, __('mixpost::auth.password_dont_match'))],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function handle(): void
    {
        $this->user->update([
            'password' => Hash::make($this->input('password')),
        ]);
    }
}
