<?php

namespace Inovector\MixpostEnterprise\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\Mixpost\Concerns\ResourceHasParameters;
use Inovector\MixpostEnterprise\Util;

class SubscriptionResource extends JsonResource
{
    use ResourceHasParameters;

    public static $wrap = null;

    public function fields(): array
    {
        return [
            'name' => $this->name,
            'workspace' => new WorkspaceResource($this->whenLoaded('workspace')),
            'platform_subscription_id' => $this->platform_subscription_id,
            'platform_plan_id' => $this->platform_plan_id,
            'plan' => $this->plan(),
            'status' => $this->status->value,
            'recurring' => $this->recurring(),
            'trial_ends_at' => $this->trial_ends_at ? Util::dateFormat($this->trial_ends_at) : null,
            'paused_from' => $this->paused_from ? Util::dateFormat($this->paused_from) : null,
            'ends_at' => $this->ends_at ? Util::dateFormat($this->ends_at) : null,
        ];
    }

    protected function plan(): ?array
    {
        if ($this->resource->relationLoaded('planMonthly')) {
            if ($this->planMonthly) {
                return [
                    'id' => $this->planMonthly->id,
                    'name' => $this->planMonthly->name,
                    'amount' => $this->planMonthly->monthly_amount,
                    'cycle' => 'monthly',
                ];
            }
        }

        if ($this->resource->relationLoaded('planYearly')) {
            if ($this->planYearly) {
                return [
                    'id' => $this->planYearly->id,
                    'name' => $this->planYearly->name,
                    'amount' => $this->planYearly->yearly_amount,
                    'cycle' => 'yearly',
                ];
            }
        }

        return null;
    }
}
