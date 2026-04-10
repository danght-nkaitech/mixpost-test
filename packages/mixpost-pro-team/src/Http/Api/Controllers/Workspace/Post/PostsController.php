<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Actions\Post\DeletePost as DeletePostAction;
use Inovector\Mixpost\Builders\Post\PostQuery;
use Inovector\Mixpost\Enums\PostDeleteMode;
use Inovector\Mixpost\Http\Api\Requests\Workspace\Post\StorePost;
use Inovector\Mixpost\Http\Api\Requests\Workspace\Post\UpdatePost;
use Inovector\Mixpost\Http\Api\Resources\PostResource;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Support\EagerLoadPostVersionsMedia;

class PostsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = PostQuery::apply($request)
            ->latest()
            ->latest('id')
            ->paginate(
                (int) min($request->get('limit', 50), 100)
            );

        EagerLoadPostVersionsMedia::apply($posts);

        return PostResource::collection($posts);
    }

    public function store(StorePost $storePost): PostResource
    {
        $record = $storePost->handle();

        $record->refresh();

        $record->load('accounts', 'versions', 'user', 'tags');

        EagerLoadPostVersionsMedia::apply($record);

        return new PostResource($record);
    }

    public function show(Request $request): PostResource
    {
        $record = Post::firstOrFailByUuid($request->route('post'));

        $record->load('accounts', 'versions', 'user', 'tags');

        EagerLoadPostVersionsMedia::apply($record);

        return new PostResource($record);
    }

    public function update(UpdatePost $updatePost): JsonResponse
    {
        return response()->json([
            'success' => (bool) $updatePost->handle(),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $result = (new DeletePostAction)(
            uuids: Arr::wrap($request->route('post')),
            mode: PostDeleteMode::from(
                $request->input('delete_mode', PostDeleteMode::APP_ONLY->value)
            ),
            toTrash: $toTrash = (bool) $request->get('trash'),
            userId: Auth::id()
        );

        if (! $toTrash) {
            return response()->json(array_merge($result, [
                'deleted' => $result['deleted_from_app'] ?? 0, // TODO: remove this on the v4
            ]));
        }

        return response()->json(array_merge($result, [
            'to_trash' => $result['deleted_from_app'] ?? 0, // TODO: remove this on the v4
        ]));
    }
}
