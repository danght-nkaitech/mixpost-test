<?php

namespace Inovector\MixpostEnterprise\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\Mixpost\Concerns\ResourceHasParameters;
use Inovector\MixpostEnterprise\Enums\PlanType;

class PlanResource extends JsonResource
{
    use ResourceHasParameters;

    public static $wrap = null;

    public function fields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'is_free' => $this->type->value === PlanType::FREE->value,
            'price' => [
                'monthly' => [
                    'amount' => $this->monthly_amount,
                    'platform_plan_id' => $this->monthly_platform_plan_id,
                ],
                'yearly' => [
                    'amount' => $this->yearly_amount,
                    'platform_plan_id' => $this->yearly_platform_plan_id,
                ],
            ],
            'trial_days' => $this->trial_days,
            'enabled' => $this->enabled,
            'sort_order' => $this->sort_order,
            'limits' => $this->limits,
        ];
    }
}
