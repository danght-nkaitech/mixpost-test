<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Http\Base\Requests\Admin\AttachWorkspaceUser as CoreAttachWorkspaceUser;

class AttachWorkspaceUser extends CoreAttachWorkspaceUser
{
    use UsesWorkspaceModel;

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['is_owner'] = ['required', 'boolean'];

        return $rules;
    }

    public function handle(): void
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($this->route('workspace'));
        $user = self::getUserClass()::find($this->input('user_id'));

        DB::transaction(function () use ($workspace, $user) {
            $workspace->attachUser(
                id: $this->input('user_id'),
                role: WorkspaceUserRole::fromName(Str::upper($this->input('role'))),
                canApprove: $this->input('can_approve', false),
            );

            // If is_owner is false and the user is the owner, delete the owner
            if (! $this->input('is_owner') && $workspace->isOwner($user)) {
                $workspace->deleteOwner();
            }

            // If is_owner is true, save the user as the owner
            if ($this->input('is_owner')) {
                $workspace->saveOwner($user);
            }
        });
    }
}
