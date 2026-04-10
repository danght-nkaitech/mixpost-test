<?php

namespace Inovector\Mixpost\Concerns\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Models\Workspace;
use InvalidArgumentException;

trait UserHasWorkspaces
{
    use UsesWorkspaceModel;

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(self::getWorkspaceModelClass(), 'mixpost_workspace_user', 'user_id', 'workspace_id')
            ->withPivot('role', 'can_approve', 'joined');
    }

    public function hasWorkspace(Workspace|int $workspace, WorkspaceUserRole|array|null $role = null): bool
    {
        if (is_array($role)) {
            foreach ($role as $roleItem) {
                if (! $roleItem instanceof WorkspaceUserRole) {
                    throw new InvalidArgumentException("'$roleItem' role must be an instance of WorkspaceUserRole");
                }
            }

            return $this->workspaces()->where('workspace_id', $workspace instanceof Workspace ? $workspace->id : $workspace)
                ->whereIn('mixpost_workspace_user.role', array_map(fn ($roleItem) => $roleItem->value, $role))
                ->exists();
        }

        return $this->workspaces()->where('workspace_id', $workspace instanceof Workspace ? $workspace->id : $workspace)
            ->when($role, function ($query) use ($role) {
                $query->where('mixpost_workspace_user.role', $role->value);
            })->exists();
    }

    public function canApprove(Workspace|int $workspace): bool
    {
        $relation = $this->workspaces()->where('workspace_id', $workspace instanceof Workspace ? $workspace->id : $workspace)->first();

        return boolval($relation->pivot->can_approve);
    }
}
