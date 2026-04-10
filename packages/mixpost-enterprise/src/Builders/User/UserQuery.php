<?php

namespace Inovector\MixpostEnterprise\Builders\User;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Contracts\Query;
use Inovector\MixpostEnterprise\Builders\User\Filters\Exclude;
use Inovector\MixpostEnterprise\Builders\User\Filters\Keyword;

class UserQuery implements Query
{
    use UsesUserModel;

    public static function apply(Request $request): Builder
    {
        $query = self::getUserClass()::query();

        if ($request->has('keyword') && $request->get('keyword')) {
            $query = Keyword::apply($query, $request->get('keyword'));
        }

        if ($request->has('exclude') && $request->get('exclude')) {
            $query = Exclude::apply($query, $request->get('exclude', []));
        }

        return $query;
    }
}
