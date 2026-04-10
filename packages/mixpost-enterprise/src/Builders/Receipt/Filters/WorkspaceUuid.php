<?php

namespace Inovector\MixpostEnterprise\Builders\Receipt\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Contracts\Filter;

class WorkspaceUuid implements Filter
{
    use UsesWorkspaceModel;

    public static function apply(Builder $builder, $value): Builder
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($value);

        return $builder->where('workspace_id', $workspace->id);
    }
}
