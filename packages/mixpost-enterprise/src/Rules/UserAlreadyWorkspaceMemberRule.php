<?php

namespace Inovector\MixpostEnterprise\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Inovector\Mixpost\Facades\WorkspaceManager;

class UserAlreadyWorkspaceMemberRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (WorkspaceManager::current()->users()->where('email', $value)->exists()) {
            $fail(__('mixpost-enterprise::team.user_already_member'));
        }
    }
}
