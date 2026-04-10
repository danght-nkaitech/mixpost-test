<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Builders\Post\PostQuery;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Calendar;
use Inovector\Mixpost\Http\Base\Resources\AccountResource;
use Inovector\Mixpost\Http\Base\Resources\PostResource;
use Inovector\Mixpost\Http\Base\Resources\TagResource;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\Tag;
use Inovector\Mixpost\Support\EagerLoadPostVersionsMedia;

class CalendarController extends Controller
{
    public function index(Calendar $request): Response
    {
        $request->handle();

        $posts = PostQuery::apply($request)->oldest('scheduled_at')->get();

        EagerLoadPostVersionsMedia::apply($posts);

        return Inertia::render('Workspace/Calendar', [
            'accounts' => fn () => AccountResource::collection(Account::oldest()->get())->resolve(),
            'tags' => fn () => TagResource::collection(Tag::latest()->get())->resolve(),
            'posts' => fn () => PostResource::collection($posts)->additional([
                'filter' => [
                    'accounts' => Arr::map($request->get('accounts', []), 'intval'),
                ],
            ]),
            'type' => $request->type(),
            'selectedDate' => $request->selectedDate(),
            'filter' => [
                'keyword' => $request->get('keyword', ''),
                'status' => $request->get('status'),
                'tags' => $request->get('tags', []),
                'accounts' => $request->get('accounts', []),
            ],
            'service_configs' => ServiceManager::exposedConfiguration(),
        ]);
    }
}
