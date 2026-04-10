<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Api\Requests\Workspace\StoreTag;
use Inovector\Mixpost\Http\Api\Requests\Workspace\UpdateTag;
use Inovector\Mixpost\Http\Api\Resources\TagResource;
use Inovector\Mixpost\Models\Tag;

class TagsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TagResource::collection(
            Tag::latest()->get()
        );
    }

    public function store(StoreTag $storeTag): TagResource
    {
        return new TagResource(
            $storeTag->handle()
        );
    }

    public function show(Request $request): TagResource
    {
        return new TagResource(
            Tag::firstOrFailByUuid($request->route('tag'))
        );
    }

    public function update(UpdateTag $updateTag): JsonResponse
    {
        return response()->json([
            'success' => (bool) $updateTag->handle(),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        return response()->json([
            'success' => (bool) Tag::where('uuid', $request->route('tag'))->delete(),
        ]);
    }
}
