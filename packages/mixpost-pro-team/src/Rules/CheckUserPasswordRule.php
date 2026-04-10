<?php

namespace Inovector\Mixpost\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class CheckUserPasswordRule implements ValidationRule
{
    public $user;

    public ?string $message;

    public function __construct($user, ?string $message = null)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Hash::check($value, $this->user->password)) {
            $fail($this->message ?: trans('validation.password'));
        }
    }
}
