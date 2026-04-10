<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Http\Base\Requests\Admin\UpdateWorkspaceUserRole as CoreUpdateWorkspaceUserRole;

class UpdateWorkspaceUserRole extends CoreUpdateWorkspaceUserRole
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
            $workspace->users()->updateExistingPivot($this->input('user_id'), [
                'role' => $this->input('role'),
                'can_approve' => $this->input('can_approve', false),
            ]);

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
