<?php

namespace Inovector\Mixpost\Http\Base\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;

class InstallationRequest extends FormRequest
{
    use UsesAuth;
    use UsesUserModel;

    public function authorize(): bool
    {
        return ! self::getUserClass()::exists();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.self::getUserClass()],
            'password' => ['required', 'confirmed', Password::defaults()],
            'timezone' => ['sometimes', 'nullable', 'timezone'],
        ];
    }

    public function handle(): void
    {
        $user = null;

        DB::transaction(function () use (&$user) {
            $user = self::getUserClass()::create([
                'name' => $this->input('name'),
                'email' => $this->input('email'),
                'password' => Hash::make($this->input('password')),
            ]);

            $user->markEmailAsVerified();

            $user->admin()->create();

            $user->settings()->create([
                'name' => 'timezone',
                'payload' => $this->input('timezone', Config::get('app.timezone')),
            ]);
        });

        self::getAuthGuard()->login($user);
    }
}
