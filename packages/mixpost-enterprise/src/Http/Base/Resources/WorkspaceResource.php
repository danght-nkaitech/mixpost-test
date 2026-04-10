<?php

namespace Inovector\MixpostEnterprise\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\ResourceHasParameters;
use Inovector\MixpostEnterprise\Util;

class WorkspaceResource extends JsonResource
{
    use ResourceHasParameters;

    public static $wrap = null;

    public function fields(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'hex_color' => "#$this->hex_color",
            'users' => UserResource::collection($this->whenLoaded('users')),
            'owner_id' => $this->owner_id,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'access_status' => $this->access_status,
            'created_at' => Util::dateTimeFormat($this->created_at),
            'subscriptions' => SubscriptionResource::collection($this->whenLoaded('subscriptions')),
            'generic_subscription' => (new PlanResource($this->whenLoaded('genericSubscriptionPlan')))
                ->only(['name'])
                ->additionalFields([
                    'free' => $this->generic_subscription_free,
                    'trial_ends_at' => $this->generic_trial_ends_at ? Util::dateFormat(Carbon::parse($this->generic_trial_ends_at)) : null,
                    'has_trial' => $this->hasGenericTrial(),
                    'has_expired_trial' => $this->hasExpiredGenericTrial(),
                    'remaining_trial_days' => $this->remainingGenericTrialDays(),
                ]),
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
