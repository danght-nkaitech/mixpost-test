<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Concerns\ConfirmsPasswords;

class EnsurePasswordConfirmed
{
    use ConfirmsPasswords;

    /**
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->passwordConfirmed()) {
            throw ValidationException::withMessages([
                'confirm_password' => [__('mixpost::auth.confirm_your_password')],
            ]);
        }

        return $next($request);
    }
}
