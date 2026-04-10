<?php

namespace Inovector\Mixpost\Http\Base\Requests\Main;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Concerns\UsesAuth;

class StoreUserToken extends FormRequest
{
    use UsesAuth;

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'expiration' => ['required', Rule::in(['days-7', 'days-30', 'days-60', 'days-90', 'never-expires', 'custom'])],
        ];

        if ($this->input('expiration') === 'custom') {
            $rules['expires_at'] = ['required', 'date'];
        }

        return $rules;
    }

    public function handle(): array
    {
        return self::getAuthGuard()->user()->createToken(
            $this->input('name'),
            $this->getExpiresAt()
        );
    }

    protected function getExpiresAt(): ?Carbon
    {
        if ($this->input('expiration') === 'days-7') {
            return Carbon::now()->addDays(7);
        }

        if ($this->input('expiration') === 'days-30') {
            return Carbon::now()->addDays(30);
        }

        if ($this->input('expiration') === 'days-60') {
            return Carbon::now()->addDays(60);
        }

        if ($this->input('expiration') === 'days-90') {
            return Carbon::now()->addDays(90);
        }

        if ($this->input('expiration') === 'never-expires') {
            return null;
        }

        return Carbon::parse($this->input('expires_at'), 'UTC');
    }
}
