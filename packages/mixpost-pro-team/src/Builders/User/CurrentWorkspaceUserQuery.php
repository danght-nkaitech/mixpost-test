<?php

namespace Inovector\Mixpost\Builders\User;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Builders\User\Filters\Exclude;
use Inovector\Mixpost\Builders\User\Filters\Keyword;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Contracts\Query;
use Inovector\Mixpost\Facades\WorkspaceManager;

class CurrentWorkspaceUserQuery implements Query
{
    use UsesUserModel;

    public static function apply(Request $request): Builder
    {
        $query = WorkspaceManager::current()->users();

        if ($request->has('keyword') && $request->get('keyword')) {
            $query = Keyword::apply($query, $request->get('keyword'));
        }

        if ($request->has('exclude') && $request->get('exclude')) {
            $query = Exclude::apply($query, $request->get('exclude', []));
        }

        return $query;
    }
}
