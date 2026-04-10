<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Concerns\UsesUserModel;

class StoreUser extends FormRequest
{
    use UsesUserModel;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.self::getUserClass()],
            'is_admin' => ['required', 'boolean'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function handle(): User
    {
        DB::transaction(function () use (&$user) {
            $user = self::getUserClass()::create([
                'name' => $this->input('name'),
                'email' => $this->input('email'),
                'password' => Hash::make($this->input('password')),
            ]);

            if ($this->input('is_admin')) {
                $user->admin()->create();
            }
        });

        return $user;
    }
}
