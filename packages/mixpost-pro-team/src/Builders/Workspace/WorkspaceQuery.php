<?php

namespace Inovector\Mixpost\Builders\Workspace;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Builders\Workspace\Filters\Exclude;
use Inovector\Mixpost\Builders\Workspace\Filters\Keyword;
use Inovector\Mixpost\Contracts\Query;
use Inovector\Mixpost\Models\Workspace;

class WorkspaceQuery implements Query
{
    public static function apply(Request $request): Builder
    {
        $query = Workspace::query();

        if ($request->has('keyword') && $request->get('keyword')) {
            $query = Keyword::apply($query, $request->get('keyword'));
        }

        if ($request->has('exclude') && $request->get('exclude')) {
            $query = Exclude::apply($query, $request->get('exclude', []));
        }

        return $query;
    }
}
