<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Actions\Post\DeletePost as DeletePostAction;
use Inovector\Mixpost\Actions\Post\RedirectAfterDeletedPost;
use Inovector\Mixpost\Builders\Post\PostQuery;
use Inovector\Mixpost\Configs\GeneralConfig;
use Inovector\Mixpost\Configs\GifConfig;
use Inovector\Mixpost\Configs\MediaConfig;
use Inovector\Mixpost\Enums\PostDeleteMode;
use Inovector\Mixpost\Facades\AIManager;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Facades\SocialProviderManager;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\DeletePost;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\StorePost;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\UpdatePost;
use Inovector\Mixpost\Http\Base\Resources\AccountResource;
use Inovector\Mixpost\Http\Base\Resources\PostResource;
use Inovector\Mixpost\Http\Base\Resources\TagResource;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\Tag;
use Inovector\Mixpost\PostingSchedule;
use Inovector\Mixpost\Support\EagerLoadPostVersionsMedia;

class PostsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|Response
    {
        $posts = PostQuery::apply($request)
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        EagerLoadPostVersionsMedia::apply($posts);

        return Inertia::render('Workspace/Posts/Index', [
            'accounts' => fn () => AccountResource::collection(Account::oldest()->get())->resolve(),
            'tags' => fn () => TagResource::collection(Tag::latest()->get())->resolve(),
            'filter' => [
                'keyword' => $request->query('keyword', ''),
                'status' => $request->query('status'),
                'tags' => $request->query('tags', []),
                'accounts' => $request->query('accounts', []),
            ],
            'posts' => fn () => PostResource::collection($posts)->additional([
                'filter' => [
                    'accounts' => Arr::map($request->query('accounts', []), 'intval'),
                ],
            ]),
            'hasNeedsApprovalPosts' => Post::needsApproval()->exists(),
            'hasFailedPosts' => Post::failed()->exists(),
            'service_configs' => ServiceManager::exposedConfiguration(),
            'supportPostDeletion' => SocialProviderManager::supportPostDeletion(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Workspace/Posts/CreateEdit', [
            //            'default_accounts' => Settings::get('default_accounts'),
            'user_can_approve' => Auth::user()->canApprove(WorkspaceManager::current()),
            'accounts' => AccountResource::collection(Account::oldest()->get())->resolve(),
            'tags' => TagResource::collection(Tag::latest()->get())->resolve(),
            'has_available_times' => PostingSchedule::hasAvailableTimes(),
            'post' => null,
            'scheduleAt' => [
                'date' => Str::before($request->route('schedule_at'), ' '),
                'time' => Str::after($request->route('schedule_at'), ' '),
            ],
            'prefill' => [
                'body' => $request->query('body', ''),
                'title' => $request->query('title', ''),
                'link' => $request->query('link', ''),
            ],
            'is_configured_service' => ServiceManager::isActive(),
            'service_configs' => ServiceManager::exposedConfiguration(),
            'general_configs' => (new GeneralConfig)->all(),
            'ai_is_ready_to_use' => AIManager::isReadyToUse(),
            'stock_photo_provider' => app(MediaConfig::class)->get('stock_photo_provider'),
            'gif_provider' => app(GifConfig::class)->get('gif_provider'),
        ]);
    }

    public function store(StorePost $storePost): RedirectResponse
    {
        $post = $storePost->handle();

        return redirect()->route('mixpost.posts.edit', ['workspace' => $post->workspace->uuid, 'post' => $post->uuid]);
    }

    public function edit(Request $request): Response
    {
        $post = Post::firstOrFailTrashedByUuid($request->route('post'));

        $post->load('accounts', 'versions', 'tags');

        EagerLoadPostVersionsMedia::apply($post);

        return Inertia::render('Workspace/Posts/CreateEdit', [
            'user_can_approve' => Auth::user()->canApprove(WorkspaceManager::current()),
            'accounts' => AccountResource::collection(Account::oldest()->get())->resolve(),
            'tags' => TagResource::collection(Tag::latest()->get())->resolve(),
            'has_available_times' => PostingSchedule::hasAvailableTimes(),
            'post' => new PostResource($post),
            'has_activities_ns' => $post->hasNotificationSubscriptionForActivities(user: Auth::id()),
            'is_configured_service' => ServiceManager::isActive(),
            'service_configs' => ServiceManager::exposedConfiguration(),
            'general_configs' => (new GeneralConfig)->all(), // TODO: Use specific config. Not all configs are needed here.
            'ai_is_ready_to_use' => AIManager::isReadyToUse(),
            'stock_photo_provider' => app(MediaConfig::class)->get('stock_photo_provider'),
            'gif_provider' => app(GifConfig::class)->get('gif_provider'),
        ]);
    }

    public function update(UpdatePost $updatePost): PostResource
    {
        $updatePost->handle();

        return new PostResource($updatePost->getPost());
    }

    public function destroy(DeletePost $request, RedirectAfterDeletedPost $redirectAfterPostDeleted): RedirectResponse
    {
        (new DeletePostAction)(
            uuids: $request->get('posts'),
            mode: PostDeleteMode::from(
                $request->input('delete_mode', PostDeleteMode::APP_ONLY->value)
            ),
            toTrash: ! ($request->get('status') === 'trash'),
            userId: Auth::id()
        );

        return $redirectAfterPostDeleted($request);
    }
}
