<?php

namespace Inovector\MixpostEnterprise\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Inovector\Mixpost\Facades\WorkspaceManager;

class MemberAlreadyInvitedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (WorkspaceManager::current()->invitations()->where('email', $value)->exists()) {
            $fail(__('mixpost-enterprise::team.member_already_invited'));
        }
    }
}
