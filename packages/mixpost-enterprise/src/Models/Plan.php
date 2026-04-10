<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\MixpostEnterprise\Casts\Amount;
use Inovector\MixpostEnterprise\Casts\PlanLimits;
use Inovector\MixpostEnterprise\Enums\PlanType;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'mixpost_e_plans';

    protected $fillable = [
        'name',
        'type',
        'monthly_amount',
        'monthly_platform_plan_id',
        'yearly_amount',
        'yearly_platform_plan_id',
        'enabled',
        'sort_order',
        'limits',
    ];

    protected $casts = [
        'type' => PlanType::class,
        'monthly_amount' => Amount::class,
        'yearly_amount' => Amount::class,
        'limits' => PlanLimits::class,
    ];

    public function scopeOrdered($query): void
    {
        $query->orderBy('sort_order', 'asc')->latest();
    }

    public function free(): bool
    {
        return $this->type->value == PlanType::FREE->value;
    }

    public function scopeFree($query): void
    {
        $query->where('type', PlanType::FREE->value);
    }

    public function scopePaid($query): void
    {
        $query->where('type', PlanType::PAID->value);
    }

    public function scopeActive($query): void
    {
        $query->where('enabled', true);
    }

    public static function platformPlanId(int|string|Model $plan, string $cycle): ?string
    {
        $model = $plan instanceof Model ? $plan : static::find($plan);

        if (! $model) {
            return null;
        }

        return match ($cycle) {
            'monthly' => $model->monthly_platform_plan_id,
            'yearly' => $model->yearly_platform_plan_id,
            default => null
        };
    }

    public static function findByPlatformPlanId(string $platformPlanId): ?static
    {
        return static::where('monthly_platform_plan_id', $platformPlanId)
            ->orWhere('yearly_platform_plan_id', $platformPlanId)->first();
    }

    public static function activeFreeExists()
    {
        return static::active()->free()->exists();
    }

    public static function activeFreeFirst()
    {
        return static::active()->free()->first();
    }

    public function used(): bool
    {
        return Workspace::genericSubscriptionByPlan($this)->exists() || Subscription::byPlan($this)->exists();
    }
}
