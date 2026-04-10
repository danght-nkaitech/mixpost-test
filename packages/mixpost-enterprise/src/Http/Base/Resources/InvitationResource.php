<?php

namespace Inovector\MixpostEnterprise\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\MixpostEnterprise\Util;

class InvitationResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'workspace' => new WorkspaceResource($this->whenLoaded('workspace')),
            'author' => new UserResource($this->whenLoaded('author')),
            'role' => $this->role,
            'can_approve' => $this->can_approve,
            'email' => $this->email,
            'created_at' => Util::dateTimeFormat($this->created_at),
        ];
    }
}
