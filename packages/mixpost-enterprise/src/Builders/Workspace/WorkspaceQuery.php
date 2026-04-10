<?php

namespace Inovector\MixpostEnterprise\Builders\Workspace;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Contracts\Query;
use Inovector\MixpostEnterprise\Builders\Workspace\Filters\AccessStatus;
use Inovector\MixpostEnterprise\Builders\Workspace\Filters\Exclude;
use Inovector\MixpostEnterprise\Builders\Workspace\Filters\GenericSubscriptionFree;
use Inovector\MixpostEnterprise\Builders\Workspace\Filters\Keyword;
use Inovector\MixpostEnterprise\Builders\Workspace\Filters\SubscriptionStatus;
use Inovector\MixpostEnterprise\Models\Workspace;

class WorkspaceQuery implements Query
{
    public static function apply(Request $request): Builder
    {
        $query = Workspace::query();

        if ($request->has('keyword') && $request->get('keyword')) {
            $query = Keyword::apply($query, $request->get('keyword'));
        }

        if ($request->has('subscription_status') && $request->get('subscription_status')) {
            $query = SubscriptionStatus::apply($query, $request->get('subscription_status'));
        }

        if ($request->has('free')) {
            $query = GenericSubscriptionFree::apply($query, true);
        }

        if ($request->has('access_status') && ! empty($request->get('access_status'))) {
            $query = AccessStatus::apply($query, $request->get('access_status', []));
        }

        if ($request->has('exclude') && $request->get('exclude')) {
            $query = Exclude::apply($query, $request->get('exclude', []));
        }

        return $query;
    }
}
