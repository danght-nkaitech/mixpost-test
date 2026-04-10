<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Events\Workspace\InvitingMember;
use Inovector\MixpostEnterprise\Mail\InvitationMail;
use Inovector\MixpostEnterprise\Rules\MemberAlreadyInvitedRule;
use Inovector\MixpostEnterprise\Rules\UserAlreadyWorkspaceMemberRule;

class InviteMember extends FormRequest
{
    use UsesAuth;
    use UsesUserModel;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', new MemberAlreadyInvitedRule, new UserAlreadyWorkspaceMemberRule],
            'role' => ['required', Rule::in(Arr::map(WorkspaceUserRole::cases(), fn ($item) => $item->value))],
            'can_approve' => ['required', 'boolean'],
        ];
    }

    public function handle(): void
    {
        $email = Str::lower(trim($this->input('email')));

        $user = self::getUserClass()::where('email', $email)->first();

        InvitingMember::dispatch(WorkspaceManager::current(), $email, $this->input('role'));

        $invitation = WorkspaceManager::current()->invitations()->create([
            'invited_by' => self::getAuthGuard()->id(),
            'user_id' => $user->id ?? null,
            'email' => $email,
            'role' => $this->input('role'),
            'can_approve' => $this->input('can_approve'),
        ]);

        Mail::to($user->email ?? $email)
            ->locale($user?->preferredLocale() ?: App::getLocale())
            ->send(new InvitationMail($invitation));
    }
}
