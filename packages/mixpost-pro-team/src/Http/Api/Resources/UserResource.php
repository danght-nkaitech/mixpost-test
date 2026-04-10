<?php

namespace Inovector\Mixpost\Http\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\Mixpost\Concerns\ResourceHasParameters;
use Inovector\Mixpost\Http\Base\Resources\WorkspaceResource;

class UserResource extends JsonResource
{
    use ResourceHasParameters;

    public static $wrap = null;

    public function fields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->resource->relationLoaded('admin') ? ($this->admin !== null) : false,
            'workspaces' => WorkspaceResource::collection($this->whenLoaded('workspaces')),
            'settings' => $this->getSettings(),
            'created_at' => $this->created_at->toDateTimeString(),
            'pivot' => $this->whenPivotLoaded('mixpost_workspace_user', function () {
                return [
                    'role' => $this->pivot->role,
                    'can_approve' => boolval($this->pivot->can_approve),
                    'joined_at' => $this->pivot->joined,
                ];
            }),
        ];
    }

    protected function getSettings()
    {
        if (! $this->resource->relationLoaded('settings')) {
            return [];
        }

        return $this->settings->pluck('payload', 'name');
    }
}
