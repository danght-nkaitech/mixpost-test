<?php

namespace Inovector\MixpostEnterprise\Http\Base\Resources;

use Illuminate\Support\Carbon;
use Inovector\Mixpost\Http\Base\Resources\UserResource as CoreUserResource;
use Inovector\MixpostEnterprise\Util;

class UserResource extends CoreUserResource
{
    public static $wrap = null;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->resource->relationLoaded('admin') ? ($this->admin !== null) : false,
            'workspaces' => WorkspaceResource::collection($this->whenLoaded('workspaces')),
            'settings' => $this->getSettings(),
            'email_verified_at' => $this->email_verified_at ? Util::dateTimeFormat($this->email_verified_at) : null,
            'created_at' => $this->created_at ? Util::dateTimeFormat($this->created_at) : null,
            'pivot' => $this->whenPivotLoaded('mixpost_workspace_user', function () {
                return [
                    'role' => $this->pivot->role,
                    'can_approve' => boolval($this->pivot->can_approve),
                    'joined_at' => Util::dateTimeFormat(Carbon::parse($this->pivot->joined)),
                ];
            }),
        ];
    }
}
