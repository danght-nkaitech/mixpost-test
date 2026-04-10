<?php

namespace Inovector\Mixpost\Builders\Post;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Builders\Post\Filters\ExcludePostStatus;
use Inovector\Mixpost\Builders\Post\Filters\PostAccounts;
use Inovector\Mixpost\Builders\Post\Filters\PostKeyword;
use Inovector\Mixpost\Builders\Post\Filters\PostScheduledAt;
use Inovector\Mixpost\Builders\Post\Filters\PostScheduledAtPeriod;
use Inovector\Mixpost\Builders\Post\Filters\PostStatus;
use Inovector\Mixpost\Builders\Post\Filters\PostTags;
use Inovector\Mixpost\Contracts\Query;
use Inovector\Mixpost\Models\Post;

class PostQuery implements Query
{
    public static function apply(Request $request): Builder
    {
        $query = Post::with('accounts', 'user', 'versions', 'tags');

        if ($request->has('status') && $request->get('status') !== null) {
            $query = PostStatus::apply($query, $request->get('status'));
        }

        if ($request->has('exclude_status') && $request->get('exclude_status')) {
            $query = ExcludePostStatus::apply($query, $request->get('exclude_status'));
        }

        if ($request->has('keyword') && $request->get('keyword')) {
            $query = PostKeyword::apply($query, $request->get('keyword'));
        }

        if ($request->has('accounts') && ! empty($request->get('accounts'))) {
            $query = PostAccounts::apply($query, $request->get('accounts', []));
        }

        if ($request->has('tags') && ! empty($request->get('tags'))) {
            $query = PostTags::apply($query, $request->get('tags', []));
        }

        if ($request->has('date') && ! empty($request->get('date')) && preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $request->get('date'))) {
            $query = PostScheduledAt::apply($query, $request->only('calendar_type', 'date'));
        }

        if ($request->has('scheduled_at_period_start') && $request->has('scheduled_at_period_end')) {
            $query = PostScheduledAtPeriod::apply($query, $request->only('scheduled_at_period_start', 'scheduled_at_period_end'));
        }

        return $query;
    }
}
