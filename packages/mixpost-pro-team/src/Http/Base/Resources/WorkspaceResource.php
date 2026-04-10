<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Util;

class WorkspaceResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'hex_color' => "#$this->hex_color",
            'users' => UserResource::collection($this->whenLoaded('users')),
            'created_at' => Util::dateTimeFormat($this->created_at),
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
