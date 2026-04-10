<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\MixpostEnterprise\Actions\Workspace\NewWorkspace;
use Inovector\MixpostEnterprise\Actions\Workspace\OnboardWorkspace;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Inovector\MixpostEnterprise\Events\User\UserCreated;
use Inovector\MixpostEnterprise\Models\Invitation;

class Register extends FormRequest
{
    use UsesAuth;
    use UsesUserModel;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.self::getUserClass()],
            'password' => ['required', 'confirmed', Password::defaults()],
            'timezone' => ['sometimes', 'nullable', 'timezone'],
            'terms' => ['required', 'accepted'],
        ];
    }

    public function handle(): void
    {
        $user = null;

        $emailVerification = (new OnboardingConfig($this))->get('email_verification');

        DB::transaction(function () use (&$user, $emailVerification) {
            $user = self::getUserClass()::create([
                'name' => $this->input('name'),
                'email' => $this->input('email'),
                'password' => Hash::make($this->input('password')),
            ]);

            if (! $emailVerification) {
                $user->markEmailAsVerified();
            }

            $user->settings()->create([
                'name' => 'timezone',
                'payload' => $this->input('timezone', Config::get('app.timezone')),
            ]);

            $workspace = (new NewWorkspace)($user);

            (new OnboardWorkspace)($workspace);
        });

        UserCreated::dispatch($user);

        self::getAuthGuard()->login($user);
    }

    public function invitation(): ?Invitation
    {
        return Invitation::findByEmail($this->input('email'));
    }
}
